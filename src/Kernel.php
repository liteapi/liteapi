<?php

namespace LiteApi;

use Exception;
use LiteApi\Command\Command;
use LiteApi\Command\CommandHandler;
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
    public const VERSION_END_OF_LIFE = '09/2023';
    public const VERSION_END_OF_MAINTENANCE = '09/2023';
    */

    private const PROPERTIES_TO_CACHE = [
        'container' => 'kernel.container',
        'router' => 'kernel.router',
        'commandHandler' => 'kernel.command'
    ];

    protected ConfigWrapper $config;
    public string $env;
    public bool $debug;
    public Router $router;
    public CommandHandler $commandHandler;
    public Container $container;
    protected Handler $eventHandler;
    protected CacheItemPoolInterface $kernelCache;
    protected ?LoggerInterface $kernelLogger = null;
    protected bool $makeCacheOnDestruct = false;
    protected bool $useCache = true;
    protected bool $isBooted = false;

    public function __construct(ConfigWrapper $config, ?CacheItemPoolInterface $kernelCache = null)
    {
        $this->config = $config;
        $this->env = $config->envParams->env;
        $this->debug = $config->envParams->debug;
        $this->kernelCache = $kernelCache === null ? $config->cache->createObject() : $kernelCache;
    }

    public function boot(): void
    {
        if ($this->useCache) {
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
        }
        $this->container = new Container();
        $this->router = new Router($this->config->trustedIps);
        $this->commandHandler = new CommandHandler();

        $classWalker = new ClassWalker();
        foreach ($this->config->servicesDir as $serviceDir) {
            $definitions = $classWalker->register($serviceDir);
            $this->loadFromDefinitions($definitions);
        }

        $extensionLoader = new ExtensionLoader($this->config->extensions);
        $extensionLoader->loadExtensions(
            $this->container,
            $this->router,
            $this->commandHandler
        );
        $this->container->createDefinitionsFromConfig($this->config->container);

        if ($this->useCache) {
            $this->makeCacheOnDestruct = true;
        }
        $this->isBooted = true;
        $this->afterBoot();
    }

    protected function afterBoot(): void
    {
        $this->ensureContainerHasKernelServices();
        $this->tryToSetKernelLogger();
        $this->eventHandler = new Handler($this->config->kernelSubscriber);
        $this->eventHandler->trigger(KernelEvent::AfterBoot, $this->container);
    }

    private function loadFromDefinitions(DefinitionsTransfer $definitions): void
    {
        $this->container->load($definitions->services);
        $this->commandHandler->load($definitions->commands);
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
            $this->kernelLogger?->error($e->getMessage(), ['exception' => $e]);
            $response = $this->router->getErrorResponse($e);
            $this->eventHandler->trigger(KernelEvent::RequestException, $e, $response);
            return $response;
        }
        $this->eventHandler->trigger(KernelEvent::AfterRequest, $response);
        return $response;
    }

    public function handleCommand(?string $commandName = null): int
    {
        if ($commandName === null) {
            $commandName = $this->commandHandler->getCommandNameFromServer();
        }
        $this->eventHandler->trigger(KernelEvent::BeforeCommand, $commandName);
        try {
            $code = $this->commandHandler->runCommandFromName($commandName, $this->container);
        } catch (Exception $e) {
            $this->kernelLogger?->error($e->getMessage(), ['exception' => $e]);
            $code = Command::FAILURE;
        }
        $this->eventHandler->trigger(KernelEvent::AfterCommand, $code);
        return $code;
    }

    public function __destruct()
    {
        if ($this->isBooted === false) {
            return;
        }
        $this->eventHandler->trigger(KernelEvent::OnDestruct, $this->container);
        if ($this->useCache && $this->makeCacheOnDestruct) {
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

    public function setUseCache(bool $useCache): void
    {
        $this->useCache = $useCache;
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

    public function getCommandHandler(): CommandHandler
    {
        return $this->commandHandler;
    }

    public function getProjectDir(): string
    {
        return $this->config->projectDir;
    }
}