<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Kernel;
use ReflectionClass;

class WarmUpCacheCommand extends KernelAwareCommand
{

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionClass = new ReflectionClass(Kernel::class);
        if ($reflectionClass->getProperty('debug')->getValue($this->kernel)) {
            $output->writeln('Cannot warmup cache when debug is true');
            return self::SUCCESS;
        }
        $newKernel = clone $this->kernel;
        $reflectionProperty = $reflectionClass->getProperty('makeCacheOnDestruct');
        $reflectionProperty->setValue($newKernel, true);
        unset($newKernel);
        return self::SUCCESS;
    }
}