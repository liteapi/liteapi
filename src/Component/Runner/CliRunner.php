<?php

namespace LiteApi\Component\Runner;

use LiteApi\Kernel;

class CliRunner implements RunnerInterface
{

    private int $result;

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
        $this->result = $this->kernel->handleCommand($this->commandName);
    }

    public function getResult(): int
    {
        return $this->result;
    }
}
