<?php

namespace LiteApi\Component\Common;

abstract class ArrayWrapper
{

    use ArrayAssertionTrait;

    public function __construct(array $config)
    {
        $this->wrap($config);
    }

    abstract protected function wrap(array $config): void;

}