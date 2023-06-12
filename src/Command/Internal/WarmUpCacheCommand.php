<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use ReflectionClass;

class WarmUpCacheCommand extends Command implements ContainerAwareInterface
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
        $reflectionClass = new ReflectionClass(Kernel::class);
        if ($reflectionClass->getProperty('debug')->getValue($this->kernel())) {
            $output->writeln('Cannot warmup kernel cache when debug is true');
            return self::SUCCESS;
        }
        $newKernel = clone $this->kernel();
        $reflectionProperty = $reflectionClass->getProperty('makeCacheOnDestruct');
        $reflectionProperty->setValue($newKernel, true);
        unset($newKernel);
        return self::SUCCESS;
    }
}