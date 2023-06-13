<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandsLoader;
use LiteApi\Container\Container;
use LiteApi\Route\Router;

class ExtensionLoader
{

    /**
     * @var array<string|int,array>
     */
    private array $extensionConfigs;

    /**
     * @param array<string|int, array> $extensionConfigs
     */
    public function __construct(array $extensionConfigs)
    {
        $this->extensionConfigs = $extensionConfigs;
    }

    public function loadExtensions(
        Container      $container,
        Router         $router,
        CommandsLoader $commandLoader
    ): void
    {
        foreach ($this->extensionConfigs as $extensionName => $extensionConfig) {
            if (is_int($extensionName)) {
                $extensionClass = $extensionConfig;
                $extensionConfig = [];
            } else {
                $extensionClass = $extensionName;
            }
            /** @var ExtensionInterface $extension */
            $extension = new $extensionClass();
            $extension->loadConfig($extensionConfig);
            $extension->validateConfig();
            $extension->registerServices($container);
            $extension->registerRoutes($router);
            $extension->registerCommands($commandLoader);
        }
    }
}