<?php

namespace LiteApi\Container;

use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\DefinedDefinition;
use LiteApi\Container\Definition\Definition;
use LiteApi\Container\Definition\InDirectDefinition;
use ReflectionClass;

trait ContainerTrait
{
    /**
     * @var array<string,Definition>
     */
    public array $definitions;

    /**
     * @param string $id
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \LiteApi\Container\ContainerNotFoundException
     */
    public function get(string $id): object
    {
        if (!isset($this->definitions[$id])) {
            throw new ContainerNotFoundException("Service $id not found");
        }
        if ($this->definitions[$id]->object === null) {
            $definition = $this->definitions[$id];
            if ($definition instanceof ClassDefinition) {
                $this->definitions[$id]->object = (new ReflectionClass($definition->name))
                    ->newInstanceArgs($definition->arguments);
            } elseif ($definition instanceof InDirectDefinition) {
                return $this->get($definition->className);
            } else {
                /** @var DefinedDefinition $definition */
                $this->definitions[$id]->object = $definition->load();
            }
        }
        return $this->definitions[$id]->object;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

}