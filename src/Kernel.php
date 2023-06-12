<?php

namespace LiteApi;

use Exception;
use LiteApi\Command\CommandsLoader;
use LiteApi\Component\Cache\ClassWalker;
use LiteApi\Component\Config\Wrapper\ConfigWrapper;
use LiteApi\Component\Event\EventHandler;
use LiteApi\Container\Container;
use LiteApi\Container\ParamsBag;
use LiteApi\Http\Request;
use LiteApi\Http\Response;
use LiteApi\Route\Router;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class Kernel
{

    public const VERSION = 005000;
    public const VERSION_DOTTED = '0.5.0';
    /* only for stable version
    public const VERSION_END_OF_LIFE = '06/2024';
    public const VERSION_END_OF_MAINTENANCE = '03/2024';
    */

    private const PROPERTIES_TO_CACHE = [
        'containerLoader' => 'kernel.container',
        'router' => 'kernel.router',
        'commandLoader' => 'kernel.command'
    ];


    public string $projectDir;
    public string $env;
    public bool $debug;
    public Router $router;
    public CommandsLoader $commandLoader;
    public Container $containerLoader;
    private EventHandler $eventHandler;
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
        $this->eventHandler = new EventHandler();
        $this->eventHandler->tryTriggering(EventHandler::KERNEL_AFTER_BOOT);
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
            $this->containerLoader = new Container();
            $this->router = new Router($config->trustedIps);
            $this->commandLoader = new CommandsLoader();
            foreach ($config->servicesDir as $serviceDir) {
                $classWalker = new ClassWalker($serviceDir);
                $classWalker->register($this->containerLoader, $this->router, $this->commandLoader);
            }
            $this->containerLoader->createDefinitionsFromConfig($config->container);
            $this->containerLoader->add(['name' => ParamsBag::class, 'args' => [$config->envParams->params]]);
        }
    }

    private function ensureContainerHasKernelServices(): void
    {
        if (!$this->containerLoader->has(__CLASS__)) {
            $this->containerLoader->add(['name' => __CLASS__, 'object' => $this]);
        }
    }

    private function tryToSetKernelLogger(): void
    {
        $definitionNames = ['kernel.logger', LoggerInterface::class];
        foreach ($definitionNames as $definitionName) {
            if ($this->containerLoader->has($definitionName)) {
                $this->kernelLogger = $this->containerLoader->get($definitionName);
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
            $this->eventHandler->tryTriggering(EventHandler::KERNEL_BEFORE_REQUEST, [$request]);
            $route = $this->router->getRoute($request);
        } catch (Exception $e) {
            return $this->router->getErrorResponse($e);
        }
        $this->containerLoader->add(['name' => Request::class, 'args' => [], 'object' => $request]);
        $response = $this->router->executeRoute($route, $this->containerLoader, $request);
        $this->eventHandler->tryTriggering(EventHandler::KERNEL_AFTER_REQUEST, [$response]);
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
        $this->eventHandler->tryTriggering(EventHandler::KERNEL_BEFORE_COMMAND, [$commandName]);
        $code = $this->commandLoader->runCommandFromName($commandName, $this->containerLoader);
        $this->eventHandler->tryTriggering(EventHandler::KERNEL_AFTER_COMMAND, [$code]);
        return $code;
    }

    public function __destruct()
    {
        $this->eventHandler->tryTriggering(EventHandler::KERNEL_ON_DESTRUCT);
        if (!$this->debug && $this->makeCacheOnDestruct) {
            unset($this->containerLoader->definitions[__CLASS__]);
            foreach (self::PROPERTIES_TO_CACHE as $property => $cacheName) {
                $cacheItem = $this->kernelCache->getItem($cacheName);
                $cacheItem->set($this->$property);
                $this->kernelCache->save($cacheItem);
            }
        }
    }
}