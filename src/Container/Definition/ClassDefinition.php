<?php

namespace LiteApi\Container\Definition;

use ReflectionClass;

class ClassDefinition extends DefinedDefinition
{

    public string $name;
    /**
     * @var string[]
     */
    public array $arguments;

    /**
     * @param string $name
     * @param string[] $arguments
     */
    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function load(): object
    {
        return (new ReflectionClass($this->name))->newInstanceArgs($this->arguments);
    }
}