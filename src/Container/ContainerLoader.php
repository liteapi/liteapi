<?php

namespace LiteApi\Container;

use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\DefinedDefinition;
use LiteApi\Container\Definition\Definition;
use LiteApi\Container\Definition\InDirectDefinition;
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
     */
    public function get(string $id): object
    {
        if (!isset($this->definitions[$id])) {
            throw new ContainerNotFoundException("Service $id not found");
        }
        if ($this->definitions[$id]->object === null) {
            $definition = $this->definitions[$id];
            if ($definition instanceof DefinedDefinition) {
                $this->definitions[$id]->object = $definition->load();
            } elseif ($definition instanceof InDirectDefinition) {
                return $this->get($definition->serviceName);
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

    /**
     * @param string[] $ids
     * @return Definition[]
     * @throws \LiteApi\Container\ContainerNotFoundException
     */
    public function getDefinitions(array $ids): array
    {
        /** @var Definition[] $definitions */
        $definitions = [];
        foreach ($ids as $id) {
            if (!isset($this->definitions[$id])) {
                throw new ContainerNotFoundException();
            }
            $definitions[] = $this->definitions[$id];
        }
        return $definitions;
    }

    /**
     * @param array<string,string> $services
     * @return void
     */
    public function prepareContainerLocator(array $services): void
    {
        foreach ($services as $internalId => $serviceId) {
            $this->definitions[$internalId] = new InDirectDefinition($serviceId);
        }
    }
}