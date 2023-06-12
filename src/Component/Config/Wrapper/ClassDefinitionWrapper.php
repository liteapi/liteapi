<?php

namespace LiteApi\Component\Config\Wrapper;

use LiteApi\Component\Util\ArrayWrapper;
use ReflectionClass;

class ClassDefinitionWrapper extends ArrayWrapper
{

    public string $class;
    public array $args;
    /**
     * @var Setter[]
     */
    public array $setters;

    protected function wrap(array $config): void
    {
        $className = 'class';
        $argsName = 'args';
        $settersName = 'setters';

        $this->assertHasKeys($config, [$className, $argsName]);
        $this->assertHasOnlyPermittedKeys($config, [$className, $argsName, $settersName]);
        $class = $config[$className];
        $this->assertIsString($class);
        $this->class = $class;

        $args = $config[$argsName];
        $this->assertList($args);
        $this->args = $args;

        $this->setters = [];
        if (isset($config[$settersName])) {
            $settersConfig = $config[$settersName];
            $this->assertIsArray($settersConfig);
            $this->assertList($settersConfig);
            foreach ($settersConfig as $setter) {
                $this->assertIsArray($setter);
                $this->setters[] = new Setter($setter);
            }
        }
    }

    public function createObject(): object
    {
        $classReflection = new ReflectionClass($this->class);
        if (!empty($this->args)) {
            $object = $classReflection->newInstanceArgs($this->args);
        } else {
            $object = new $this->class();
        }
        foreach ($this->setters as $setter) {
            $reflectionMethod = $classReflection->getMethod($setter->method);
            $reflectionMethod->invokeArgs($object, $setter->args);
        }
        return $object;
    }


}