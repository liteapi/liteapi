<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\AsCommand;
use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use ReflectionClass;

#[AsCommand('cache:warmup')]
class CacheWarmup extends Command implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    protected function kernel(): Kernel
    {
        return $this->container->get(Kernel::class);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->kernel()->getKernelCache()->clear();
        $kernel = clone $this->kernel();
        $reflectionClass = new ReflectionClass($kernel);
        $property = $reflectionClass->getProperty('makeCacheOnDestruct');
        $property->setValue($kernel, true);
        unset($kernel);
        return self::SUCCESS;
    }
}