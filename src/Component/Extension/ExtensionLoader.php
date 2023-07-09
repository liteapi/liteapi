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

    public function loadExtensions(Container $container, Router $router, CommandHandler $commandHandler): void
    {
        foreach ($this->extensionConfigs as $extensionName => $extensionConfig) {
            $extension = $this->loadClass($extensionName, $extensionConfig);
            $extension->registerServices($container);
            $extension->registerRoutes($router);
            $extension->registerCommands($commandHandler);
        }
    }

    private function loadClass(string|int $extensionName, string|array $extensionConfig): Extension
    {
        if (is_int($extensionName)) {
            $extensionClass = $extensionConfig;
            $extensionConfig = [];
        } else {
            $extensionClass = $extensionName;
        }
        /** @var Extension $extension */
        $extension = new $extensionClass();
        $extension->loadConfig($extensionConfig);
        $extension->validateConfig();
        return $extension;
    }

    public function loadFiles(string $projectDir): void
    {
        foreach ($this->extensionConfigs as $extensionName => $extensionConfig) {
            $extension = $this->loadClass($extensionName, $extensionConfig);
            $extension->loadFiles($projectDir);
        }
    }
}