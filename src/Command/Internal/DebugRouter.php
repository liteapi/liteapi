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
        $router = $this->kernel()->getRouter();
        $output->writeln('Path    Http methods');
        foreach ($router->routes as $route) {
            $output->writeln($route->path . '   ' . implode(', ', $route->httpMethods));
        }
        return self::SUCCESS;
    }
}