<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\CommandsLoader;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use ReflectionClass;

class DebugCommand extends Command implements ContainerAwareInterface
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
        $commandLoaderReflection = $reflectionKernel->getProperty('commandLoader');
        /** @var CommandsLoader $commandLoader */
        $commandLoader = $commandLoaderReflection->getValue($this->kernel());
        $output->writeln(array_keys($commandLoader->command));
        return self::SUCCESS;
    }
}