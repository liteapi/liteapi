<?php

namespace LiteApi\Component\Loader;

use LiteApi\Command\AsCommand;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Exception\ProgrammerException;
use LiteApi\Route\Attribute\AsRoute;
use LiteApi\Route\Attribute\OnError;
use LiteApi\Route\Route;
use ReflectionClass;

class ClassWalker
{

    /**
     * @param string $servicePath
     * @return DefinitionsTransfer
     * @throws ProgrammerException
     * @throws \ReflectionException
     */
    public function register(
        string $servicePath
    ): DefinitionsTransfer
    {
        $services = [];
        $commands = [];
        $routes = [];
        $onError = [];
        if (is_file($servicePath) || !is_dir($servicePath)) {
            throw new ProgrammerException('Cannot load path that is not directory');
        }
        $classFinder = new ClassFinder();
        foreach ($classFinder->getAllClassInDir($servicePath) as $className) {
            /* Add to container */
            $services[$className] = new ClassDefinition($className, []);
            /* Add routes or command if exists */
            $reflectionClass = new ReflectionClass($className);
            $commandAttribute = $reflectionClass->getAttributes(AsCommand::class);
            if (!empty($commandAttribute)) {
                $commands[$commandAttribute[0]->getArguments()[0]] = $className;
            }
            foreach ($reflectionClass->getMethods() as $method) {
                $attributes = $method->getAttributes(AsRoute::class);
                if (!empty($attributes)) {
                    $attribute = $attributes[0];
                    $arguments = $attribute->getArguments();
                    $routes[] = new Route($className,
                        $method->getName(),
                        $arguments[0],
                        $arguments[1] ?? []
                    );
                }
                $attributes = $method->getAttributes(OnError::class);
                if (!empty($attributes)) {
                    $attribute = $attributes[0];
                    $arguments = $attribute->getArguments();
                    $onError[$arguments[0]->value] = $className . '::' . $method->getName();
                }
            }
        }
        return new DefinitionsTransfer($services, $commands, $routes, $onError);
    }
}