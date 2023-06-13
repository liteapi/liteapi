<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Container\Container;
use LiteApi\Kernel;
use ReflectionClass;

class DebugContainerCommand extends Command implements ContainerAwareInterface
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
        $containerLoaderReflection = $reflectionKernel->getProperty('container');
        /** @var Container $container */
        $container = $containerLoaderReflection->getValue($this->kernel());
        $output->writeln(array_keys($container->definitions));
        return self::SUCCESS;
    }
}