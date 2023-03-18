<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Command;
use LiteApi\Kernel;

abstract class KernelAwareCommand extends Command
{

    protected Kernel $kernel;

    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }
}