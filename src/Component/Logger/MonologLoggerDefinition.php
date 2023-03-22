<?php

namespace LiteApi\Component\Logger;

use LiteApi\Component\Env;
use Monolog\Logger;
use LiteApi\Container\Definition\DefinedDefinition;

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
            $logger->pushHandler(Env::createClassFromConfig($handler));
        }
        foreach ($this->processors as $processor) {
            $logger->pushProcessor(Env::createClassFromConfig($processor));
        }
        return $logger;
    }
}