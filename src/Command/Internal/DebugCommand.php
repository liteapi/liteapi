<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;

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
        $commandLoader = $this->kernel()->getCommandHandler();
        $output->writeln('Command    :   Class');
        foreach ($commandLoader->command as $command => $class) {
            $output->writeln($command . '     ' . $class);
        }
        return self::SUCCESS;
    }
}