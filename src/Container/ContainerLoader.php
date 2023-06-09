<?php

namespace LiteApi\Container;

use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\DefinedDefinition;
use LiteApi\Container\Definition\Definition;
use LiteApi\Container\Definition\InDirectDefinition;
use LiteApi\Exception\KernelException;
use LiteApi\Exception\ProgrammerException;
use Psr\Container\ContainerInterface;

class ContainerLoader implements ContainerInterface
{

    /**
     * @var array<string,Definition>
     */
    public array $definitions;

    /**
     * @param array<string, string|array> $config
     * @throws \Exception
     */
    public function createDefinitionsFromConfig(array $config): void
    {
        foreach ($config as $name => $value) {
            if (is_string($value) && str_starts_with($value, '@')) {
                $this->definitions[$name] = new InDirectDefinition(substr($value, 1));
            } elseif (class_exists($name)) {
                $this->definitions[$name] = new ClassDefinition($name, $value);
            } elseif (interface_exists($name)) {
                $this->definitions[$name] = new InDirectDefinition($value);
            } else {
                throw new ProgrammerException('Invalid container definition for name ' . $name);
            }
        }
    }

    /**
     * @param string $id
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \LiteApi\Container\ContainerNotFoundException
     * @throws KernelException
     */
    public function get(string $id): object
    {
        if (!isset($this->definitions[$id])) {
            throw new ContainerNotFoundException(sprintf('Service %s not found', $id));
        }
        $definition = $this->definitions[$id];
        if ($definition->object === null) {
            if ($definition instanceof DefinedDefinition) {
                $definition->object = $definition->load();
            } elseif ($definition instanceof InDirectDefinition) {
                return $this->get($definition->serviceName);
            } else {
                throw new KernelException(sprintf(
                    'Undefined definition class %s in container. Cannot load object from definition of id %s',
                    $definition::class, $id));
            }
            if (is_subclass_of($definition->object, ContainerAwareInterface::class)) {
                $definition->object->setContainer($this);
            }
        }
        return $definition->object;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * You must use name and args. Optionally you can provide object
     *
     * @param array $definition
     */
    public function add(array $definition): void
    {
        $newDefinition = new ClassDefinition($definition['name'], $definition['args'] ?? []);
        $newDefinition->object = $definition['object'] ?? null;
        $this->definitions[$definition['name']] = $newDefinition;
    }

    /**
     * @param array<string,Definition> $definitions
     * @return void
     */
    public function addDefinitions(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->definitions[$id] = $definition;
        }
    }
}