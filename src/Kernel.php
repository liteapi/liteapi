<?php

namespace LiteApi;

use Exception;
use LiteApi\Command\CommandsLoader;
use LiteApi\Component\Config\Wrapper\ConfigWrapper;
use LiteApi\Component\Extension\ExtensionLoader;
use LiteApi\Component\Loader\ClassWalker;
use LiteApi\Component\Loader\DefinitionsTransfer;
use LiteApi\Container\Container;
use LiteApi\Event\Handler;
use LiteApi\Event\KernelEvent;
use LiteApi\Http\Request;
use LiteApi\Http\Response;
use LiteApi\Route\Router;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class Kernel
{

    public const VERSION = 000600;
    public const VERSION_DOTTED = '0.6.0';
    /* only for stable version
    public const VERSION_END_OF_LIFE = '06/2024';
    public const VERSION_END_OF_MAINTENANCE = '03/2024';
    */

    private const PROPERTIES_TO_CACHE = [
        'container' => 'kernel.container',
        'router' => 'kernel.router',
        'commandLoader' => 'kernel.command'
    ];

    public string $projectDir;
    public string $env;
    public bool $debug;
    public Router $router;
    public CommandsLoader $commandLoader;
    public Container $container;
    protected Handler $eventHandler;
    protected CacheItemPoolInterface $kernelCache;
    protected ?LoggerInterface $kernelLogger = null;
    protected bool $makeCacheOnDestruct = false;

    public function __construct(ConfigWrapper $config, ?CacheItemPoolInterface $kernelCache = null)
    {
        $this->projectDir = $config->projectDir;
        $this->env = $config->envParams->env;
        $this->debug = $config->envParams->debug;
        $this->kernelCache = $kernelCache === null ? $config->cache->createObject() : $kernelCache;
        $this->boot($config);
        $this->ensureContainerHasKernelServices();
        $this->tryToSetKernelLogger();

        $this->eventHandler = new Handler($config->kernelSubscriber);
        $this->eventHandler->trigger(KernelEvent::AfterBoot, $this->container);
    }

    protected function boot(ConfigWrapper $config): void
    {
        $loaded = true;
        foreach (self::PROPERTIES_TO_CACHE as $property => $cacheName) {
            $routerItem = $this->kernelCache->getItem($cacheName);
            if (!$routerItem->isHit()) {
                $loaded = false;
                break;
            }
            $this->$property = $routerItem->get();
        }
        if ($loaded) {
            return;
        }
        $this->container = new Container();
        $this->router = new Router($config->trustedIps);
        $this->commandLoader = new CommandsLoader();

        $classWalker = new ClassWalker();
        foreach ($config->servicesDir as $serviceDir) {
            $definitions = $classWalker->register($serviceDir);
            $this->loadFromDefinitions($definitions);
        }

        $extensionLoader = new ExtensionLoader($config->extensions);
        $extensionLoader->loadExtensions(
            $this->container,
            $this->router,
            $this->commandLoader
        );
        $this->container->createDefinitionsFromConfig($config->container);

        $this->makeCacheOnDestruct = true;
    }

    private function loadFromDefinitions(DefinitionsTransfer $definitions): void
    {
        $this->container->load($definitions->services);
        $this->commandLoader->load($definitions->commands);
        $this->router->load($definitions->routes);
        $this->router->loadOnError($definitions->onErrors);
    }

    private function ensureContainerHasKernelServices(): void
    {
        if (!$this->container->has(__CLASS__)) {
            $this->container->add(['name' => __CLASS__, 'object' => $this]);
        }
    }

    private function tryToSetKernelLogger(): void
    {
        $definitionNames = ['kernel.logger', LoggerInterface::class];
        foreach ($definitionNames as $definitionName) {
            if ($this->container->has($definitionName)) {
                $this->kernelLogger = $this->container->get($definitionName);
                return;
            }
        }
    }

    public function handleRequest(Request $request): Response
    {
        $this->kernelLogger?->info($request->getRequestLogMessage());
        try {
            $this->router->validateIp($request->ip);
            $this->eventHandler->trigger(KernelEvent::BeforeRequest, $request);
            $route = $this->router->getRoute($request);
            $this->container->add(['name' => Request::class, 'args' => [], 'object' => $request]);
            $response = $route->execute($this->container, $request);
        } catch (Exception $e) {
            try {
                $this->eventHandler->trigger(KernelEvent::RequestException, $e);
            } catch (Exception $eventException) {
                $this->kernelLogger?->error($e->getMessage(), ['exception' => $e]);
                return $this->router->getErrorResponse($eventException);
            }
            $this->kernelLogger?->error($e->getMessage(), ['exception' => $e]);
            return $this->router->getErrorResponse($e);
        }
        $this->eventHandler->trigger(KernelEvent::AfterRequest, $response);
        return $response;
    }

    public function handleCommand(?string $commandName = null): int
    {
        if ($commandName === null) {
            $commandName = $this->commandLoader->getCommandNameFromServer();
        }
        $this->eventHandler->trigger(KernelEvent::BeforeCommand, $commandName);
        $code = $this->commandLoader->runCommandFromName($commandName, $this->container);
        $this->eventHandler->trigger(KernelEvent::AfterCommand, $code);
        return $code;
    }

    public function __destruct()
    {
        $this->eventHandler->trigger(KernelEvent::OnDestruct, $this->container);
        if ($this->makeCacheOnDestruct) {
            $this->prepareContainerToCache();
            foreach (self::PROPERTIES_TO_CACHE as $property => $cacheName) {
                $cacheItem = $this->kernelCache->getItem($cacheName);
                $cacheItem->set($this->$property);
                $this->kernelCache->save($cacheItem);
            }
        }
    }

    protected function prepareContainerToCache(): void
    {
        unset($this->container->definitions[__CLASS__]);
        if ($this->container->has(Request::class)) {
            unset($this->container->definitions[Request::class]);
        }
    }

    public function getKernelCache(): CacheItemPoolInterface
    {
        return $this->kernelCache;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getCommandLoader(): CommandsLoader
    {
        return $this->commandLoader;
    }
}