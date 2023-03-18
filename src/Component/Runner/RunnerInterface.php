<?php

namespace LiteApi\Component\Runner;

interface RunnerInterface
{
    /**
     * Run method is meant to be a main method to runners
     * @return void
     */
    public function run(): void;

}