<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandHandler;
use LiteApi\Container\Container;
use LiteApi\Route\Router;

class ExtensionLoader
{

    /**
     * @param array<string|int, array> $extensionConfigs
     */
    public function __construct(
        public array $extensionConfigs)
    {
    }

    public function loadExtensions(
        Container      $container,
        Router         $router,
        CommandHandler $commandLoader
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