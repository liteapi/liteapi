<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class CacheClearCommand extends Command implements ContainerAwareInterface
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
        /** @var AbstractAdapter $adapter */
        $adapter = $reflectionClass->getProperty('kernelCache')->getValue($this->kernel());
        $adapter->clear();
        return self::SUCCESS;
    }
}