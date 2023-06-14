<?php

namespace LiteApi;

use Exception;
use LiteApi\Command\CommandsLoader;
use LiteApi\Component\Cache\ClassWalker;
use LiteApi\Component\Config\Wrapper\ConfigWrapper;
use LiteApi\Container\Container;
use LiteApi\Container\ParamsBag;
use LiteApi\Event\Handler;
use LiteApi\Event\KernelEvent;
use LiteApi\Http\Request;
use LiteApi\Http\Response;
use LiteApi\Route\Router;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class Kernel
{

    public const VERSION = 006000;
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
    private Handler $eventHandler;
    private AbstractAdapter $kernelCache;
    private ?LoggerInterface $kernelLogger = null;
    private bool $makeCacheOnDestruct = true;

    public function __construct(ConfigWrapper $config)
    {
        $this->projectDir = $config->projectDir;
        $this->env = $config->envParams->env;
        $this->debug = $config->envParams->debug;
        $this->kernelCache = $config->cache->createObject();
        $this->boot($config);
        $this->ensureContainerHasKernelServices();
        $this->tryToSetKernelLogger();
        $this->eventHandler = new Handler($config->kernelSubscriber);
        $this->eventHandler->trigger(KernelEvent::AfterBoot, $this->container);
    }

    protected function boot(ConfigWrapper $config): void
    {
        $loaded = false;
        if (!$this->debug) {
            $loaded = true;
            foreach (self::PROPERTIES_TO_CACHE as $property => $cacheName) {
                $routerItem = $this->kernelCache->getItem($cacheName);
                if (!$routerItem->isHit()) {
                    $loaded = false;
                    break;
                }
                $this->$property = $routerItem->get();
            }
            $this->makeCacheOnDestruct = false;
        }
        if (!$loaded) {
            $this->container = new Container();
            $this->router = new Router($config->trustedIps);
            $this->commandLoader = new CommandsLoader();
            foreach ($config->servicesDir as $serviceDir) {
                $classWalker = new ClassWalker($serviceDir);
                $classWalker->register($this->container, $this->router, $this->commandLoader);
            }
            $this->container->createDefinitionsFromConfig($config->container);
            $this->container->add(['name' => ParamsBag::class, 'args' => [$config->envParams->params]]);
        }
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

    /**
     * @param Request $request
     * @return Response
     */
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
            $this->eventHandler->trigger(KernelEvent::RequestException, $e);
            return $this->router->getErrorResponse($e);
        }
        $this->eventHandler->trigger(KernelEvent::AfterRequest, $response);
        return $response;
    }

    /**
     * @param string|null $commandName
     * @return int
     */
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
        if (!$this->debug && $this->makeCacheOnDestruct) {
            unset($this->container->definitions[__CLASS__]);
            foreach (self::PROPERTIES_TO_CACHE as $property => $cacheName) {
                $cacheItem = $this->kernelCache->getItem($cacheName);
                $cacheItem->set($this->$property);
                $this->kernelCache->save($cacheItem);
            }
        }
    }
}