<?php

namespace LiteApi\Component\Extension;

use LiteApi\Command\CommandHandler;
use LiteApi\Component\Common\ArrayAssertionTrait;
use LiteApi\Container\Container;
use LiteApi\Route\Router;

class Extension implements ExtensionInterface
{

    use ArrayAssertionTrait;

    protected array $config = [];

    public function loadConfig(array $config): void
    {
        $this->config = $config;
    }

    public function validateConfig(): void
    {

    }

    public function registerServices(Container $container): void
    {

    }

    public function registerRoutes(Router $router): void
    {

    }

    public function registerCommands(CommandHandler $commandLoader): void
    {

    }
}