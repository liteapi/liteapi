<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandHandler;
use LiteApi\Container\Container;
use LiteApi\Route\Router;

interface ExtensionInterface
{

    public function loadConfig(array $config): void;

    public function validateConfig(): void;

    public function registerServices(Container $container): void;

    public function registerRoutes(Router $router): void;

    public function registerCommands(CommandHandler $commandLoader): void;

}