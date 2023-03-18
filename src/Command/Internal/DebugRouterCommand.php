<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Route\Router;
use ReflectionClass;

class DebugRouterCommand extends KernelAwareCommand
{

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionKernel = new ReflectionClass($this->kernel);
        $routerReflection = $reflectionKernel->getProperty('router');
        /** @var Router $router */
        $router = $routerReflection->getValue($this->kernel);

        $names = [];
        foreach ($router->routes as $route) {
            $names[] = $route->path;
        }

        $output->writeln($names);

        return self::SUCCESS;
    }
}