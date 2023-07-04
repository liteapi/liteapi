<?php

namespace LiteApi\Component\Runner;

use LiteApi\Kernel;

class CliRunner implements RunnerInterface
{


    public function __construct(
        private readonly Kernel  $kernel,
        private readonly ?string $commandName = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->kernel->boot();
        $result = $this->kernel->handleCommand($this->commandName);
        exit($result);
    }
}
