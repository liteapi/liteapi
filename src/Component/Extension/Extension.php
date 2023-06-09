<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandsLoader;
use LiteApi\Container\ContainerLoader;
use LiteApi\Route\Router;

class Extension implements ExtensionInterface
{

    protected array $config = [];

    public function loadConfig(array $config): void
    {
        $this->config = $config;
    }

    public static function validateConfig(array $config): void
    {

    }

    public function registerServices(ContainerLoader $container): void
    {

    }

    public function registerRoutes(Router $router): void
    {

    }

    public function registerCommands(CommandsLoader $commandLoader): void
    {

    }
}