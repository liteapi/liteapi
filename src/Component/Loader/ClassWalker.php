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
        $onErrors = [];
        if (is_file($servicePath) || !is_dir($servicePath)) {
            throw new ProgrammerException('Cannot load path that is not directory');
        }
        $classFinder = new ClassFinder();
        foreach ($classFinder->getAllClassInDir($servicePath) as $className) {
            /* Add to container */
            $services[$className] = new ClassDefinition($className, []);
            /* Add routes or command if exists */
            $reflectionClass = new ReflectionClass($className);
            $commandAttributes = $reflectionClass->getAttributes(AsCommand::class);
            if (!empty($commandAttributes)) {
                /** @var AsCommand $command */
                $command = $commandAttributes[0]->newInstance();
                $commands[$command->name] = $className;
            }
            foreach ($reflectionClass->getMethods() as $method) {
                $attributes = $method->getAttributes(AsRoute::class);
                if (!empty($attributes)) {
                    /** @var AsRoute $asRoute */
                    $asRoute = $attributes[0]->newInstance();
                    $routes[] = new Route($className,
                        $method->getName(),
                        $asRoute->path,
                        $asRoute->methods
                    );
                }
                $attributes = $method->getAttributes(OnError::class);
                foreach ($attributes as $attribute) {
                    /** @var OnError $onError */
                    $onError = $attribute->newInstance();
                    $onErrors[$onError->status->value] = $className . '::' . $method->getName();
                }
            }
        }
        return new DefinitionsTransfer($services, $commands, $routes, $onErrors);
    }
}