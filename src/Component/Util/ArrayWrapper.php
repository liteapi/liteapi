<?php

namespace LiteApi\Component\Util;

use Exception;
use LiteApi\Exception\ProgrammerException;

abstract class ArrayWrapper
{

    public function __construct(array $config)
    {
        $this->wrap($config);
    }

    abstract protected function wrap(array $config): void;

    protected function assertList(array $item): void
    {
        if (!array_is_list($item)) {
            throw new ProgrammerException(sprintf('Array %s should be list', var_export($item, true)));
        }
    }

    protected function assertIsArray(mixed $item): void
    {
        if (!is_array($item)) {
            throw new ProgrammerException(sprintf('Value %s should be array, %s given',
                var_export($item, true), gettype($item)));
        }
    }

    protected function assertIsString(mixed $item): void
    {
        if (!is_string($item)) {
            throw new ProgrammerException(sprintf('Value %s should be string, %s given',
                var_export($item, true), gettype($item)));
        }
    }

    protected function assertHasKeys(array $item, array $keys): void
    {
        foreach ($keys as $key) {
            if (!isset($item[$key])) {
                throw new ProgrammerException(sprintf('Item %s should has key %s',
                    var_export($item, true), $key));
            }
        }
    }

}