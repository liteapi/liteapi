<?php

namespace LiteApi\Event;

use LiteApi\Exception\ProgrammerException;
use ReflectionClass;

class Handler
{

    public string $className;
    public array $definitions;

    /**
     * @param string|null $className
     * @throws ProgrammerException
     */
    public function __construct(?string $className)
    {
        if ($className == null) {
            $this->className = '';
            $this->definitions = [];
        } else {
            if (!is_subclass_of($className, KernelEventSubscriberInterface::class)) {
                throw new ProgrammerException('Kernel event subscriber must be a subclass of ' . KernelEventSubscriberInterface::class);
            }
            $this->className = $className;
            $this->definitions = $className::getEventsDefinitions();
        }
    }

    /**
     * @param KernelEvent $event
     * @param ...$args
     * @return void
     * @throws ProgrammerException
     * @throws \ReflectionException
     */
    public function trigger(KernelEvent $event, ...$args): void
    {
        if (!isset($this->definitions[$event->value])) {
            return;
        }
        $definitions = $this->definitions[$event->value];
        if (!is_array($definitions)) {
            throw new ProgrammerException(sprintf('Definitions from event %s are not of type array', $event->value));
        }
        if (rsort($definitions, SORT_NUMERIC)) {
            throw new ProgrammerException(sprintf(
                'Cannot sort numeric definitions from event %s. Ensure that keys are numerical', $event->value));
        }
        $reflectionClass = new ReflectionClass($this->className);
        $kernelEventClass = $reflectionClass->newInstanceWithoutConstructor();
        foreach ($definitions as $methodName) {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            $reflectionMethod->invoke($kernelEventClass, $args);
        }
    }

}