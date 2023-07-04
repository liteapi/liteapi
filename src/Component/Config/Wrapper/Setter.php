<?php

namespace LiteApi\Component\Config\Wrapper;

use LiteApi\Component\Common\ArrayWrapper;

class Setter extends ArrayWrapper
{

    public string $method;
    public array $args;

    protected function wrap(array $config): void
    {
        $methodName = 'method';
        $argsName = 'args';
        $this->assertHasKeys($config, [$methodName, $argsName]);
        $this->assertHasOnlyPermittedKeys($config, [$methodName, $argsName]);

        $method = $config[$methodName];
        $this->assertIsString($method);
        $this->method = $method;

        $args = $config[$argsName];
        $this->assertIsArray($args);
        $this->assertList($args);
        $this->args = $args;
    }
}