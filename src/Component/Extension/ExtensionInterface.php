<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandsLoader;
use LiteApi\Container\ContainerLoader;
use LiteApi\Route\Router;

interface ExtensionInterface
{

    public function loadConfig(array $config): void;

    public function registerServices(ContainerLoader $container): void;

    public function registerRoutes(Router $router): void;

    public function registerCommands(CommandsLoader $commandLoader): void;

}