<?php

namespace LiteApi\Component\Runner;

use LiteApi\Kernel;

class CliRunner implements RunnerInterface
{

    private Kernel $kernel;
    public ?string $commandName;

    public function __construct(Kernel $kernel, ?string $commandName = null)
    {
        $this->kernel = $kernel;
        $this->commandName = $commandName;
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $result = $this->kernel->handleCommand($this->commandName);
        exit($result);
    }
}
