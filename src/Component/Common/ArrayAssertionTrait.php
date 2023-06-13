<?php

namespace LiteApi\Component\Common;

use LiteApi\Exception\ProgrammerException;

trait ArrayAssertionTrait
{

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

    protected function assertHasOnlyPermittedKeys(array $item, array $keys): void
    {
        $notPermittedKeys = array_diff(array_keys($item), $keys);
        if (!empty($notPermittedKeys)) {
            throw new ProgrammerException('Array has not permitted keys: ' . implode(', ', $notPermittedKeys));
        }
    }

}