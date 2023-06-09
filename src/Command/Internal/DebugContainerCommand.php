<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\ContainerLoader;
use ReflectionClass;

class DebugContainerCommand extends KernelAwareCommand
{

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionKernel = new ReflectionClass($this->kernel);
        $containerLoaderReflection = $reflectionKernel->getProperty('containerLoader');
        /** @var ContainerLoader $containerLoader */
        $containerLoader = $containerLoaderReflection->getValue($this->kernel);
        $output->writeln(array_keys($containerLoader->definitions));
        return self::SUCCESS;
    }
}