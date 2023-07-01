<?php

namespace LiteApi\Component\Runner;

use LiteApi\Http\Request;
use LiteApi\Kernel;

class HttpRunner implements RunnerInterface
{

    private Request $request;

    public function __construct(
        private readonly Kernel $kernel
    )
    {
        $this->request = Request::makeFromGlobals();
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $response = $this->kernel->handleRequest($this->request);
        $response->send();
    }
}