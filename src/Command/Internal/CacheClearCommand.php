<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Kernel;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class CacheClearCommand extends KernelAwareCommand
{

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionClass = new ReflectionClass(Kernel::class);
        /** @var AbstractAdapter $adapter */
        $adapter = $reflectionClass->getProperty('kernelCache')->getValue($this->kernel);
        $adapter->clear();
        return self::SUCCESS;
    }
}