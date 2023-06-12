<?php

namespace LiteApi\Component\Logger;

use LiteApi\Component\Object\ObjectWrapper;
use LiteApi\Container\Definition\DefinedDefinition;
use Monolog\Logger;

class MonologLoggerDefinition extends DefinedDefinition
{

    public string $name;
    public array $handlers;
    public array $processors;

    public function __construct(string $name, array $handlers = [], array $processors = [])
    {
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }

    public function load(): object
    {
        $logger = new Logger($this->name);
        foreach ($this->handlers as $handler) {
            $logger->pushHandler(ObjectWrapper::parseArrayToObject($handler));
        }
        foreach ($this->processors as $processor) {
            $logger->pushProcessor(ObjectWrapper::parseArrayToObject($processor));
        }
        return $logger;
    }
}