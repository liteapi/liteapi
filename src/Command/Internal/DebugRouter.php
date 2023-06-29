<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use LiteApi\Route\Router;
use ReflectionClass;

class DebugRouter extends Command implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    private function kernel(): Kernel
    {
        return $this->container->get(Kernel::class);
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionKernel = new ReflectionClass($this->kernel());
        $routerReflection = $reflectionKernel->getProperty('router');
        /** @var Router $router */
        $router = $routerReflection->getValue($this->kernel());
        $names = [];
        foreach ($router->routes as $route) {
            $names[] = $route->path;
        }
        $output->writeln($names);
        return self::SUCCESS;
    }
}