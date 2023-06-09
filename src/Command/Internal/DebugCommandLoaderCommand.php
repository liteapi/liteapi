<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\CommandsLoader;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use ReflectionClass;

class DebugCommandLoaderCommand extends KernelAwareCommand
{

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionKernel = new ReflectionClass($this->kernel);
        $commandLoaderReflection = $reflectionKernel->getProperty('commandLoader');
        /** @var CommandsLoader $commandLoader */
        $commandLoader = $commandLoaderReflection->getValue($this->kernel);
        $output->writeln(array_keys($commandLoader->command));
        return self::SUCCESS;
    }
}