<?php

namespace LiteApi\Component\Cache\Command;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class SymfonyCacheClearCommand extends Command implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    protected function kernel(): Kernel
    {
        return $this->container->get(Kernel::class);
    }

//    public function prepare(InputInterface $input): void
//    {
//        $input->addArgument('pool');
//    }

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