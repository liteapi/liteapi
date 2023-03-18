<?php

namespace LiteApi\Test\resources\classes;

use LiteApi\Command\AsCommand;
use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;

#[AsCommand('command:one')]
class CommandOne extends Command
{

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('CommandOne is running');
        return self::SUCCESS;
    }
}