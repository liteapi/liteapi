<?php

namespace LiteApi\Command;

use Exception;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Input\Stdin;
use LiteApi\Command\Internal\CacheClear;
use LiteApi\Command\Internal\CacheWarmup;
use LiteApi\Command\Internal\DebugCommand;
use LiteApi\Command\Internal\DebugContainer;
use LiteApi\Command\Internal\DebugRouter;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Command\Output\Stdout;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Container;
use LiteApi\Container\Definition\ClassDefinition;
use ReflectionClass;
use ReflectionNamedType;

class CommandHandler
{

    private const KERNEL_COMMANDS = [
        'cache:warmup' => CacheWarmup::class,
        'cache:clear' => CacheClear::class,
        'debug:container' => DebugContainer::class,
        'debug:router' => DebugRouter::class,
        'debug:command' => DebugCommand::class
    ];

    /**
     * @var array<string,string>
     */
    public array $command = [];

    
    public function __construct()
    {
        $this->command = self::KERNEL_COMMANDS;
    }

    public function registerCommand(string $commandName, string $className): void
    {
        $this->command[$commandName] = $className;
    }

    public function runCommandFromName(
        string $commandName,
        Container $container,
        ?InputInterface $input = null,
        ?OutputInterface $output = null
    ): int
    {
        if ($output === null){
            $output = new Stdout();
        }
        try {
            $className = $this->command[$commandName];
            $reflectionClass = new ReflectionClass($className);
            $constructor = $reflectionClass->getConstructor();
            if ($constructor !== null) {
                if ($container->has($className)) {
                    /** @var ClassDefinition $classDefinition */
                    $classDefinition = $container->get($className);
                    $args = $classDefinition->arguments;
                } else {
                    $args = [];
                }
                foreach ($constructor->getParameters() as $parameter) {
                    $parameterType = $parameter->getType();
                    if ($parameterType instanceof ReflectionNamedType) {
                        $args[] = $container->get($parameterType->getName());
                    }
                }
            } else {
                $args = [];
            }
            if ($input === null) {
                $input = new Stdin();
            }
            /** @var Command $command */
            $command = $reflectionClass->newInstanceArgs($args);
            $command->prepare($input);
            /* Prepare input */
            $input->load();
            /* Inject services */
            if (is_subclass_of($command, ContainerAwareInterface::class)) {
                $command->/** @scrutinizer ignore-call */setContainer($container);
            }
            return $command->execute($input, $output);
        } catch (Exception $e) {
            $output->writeln([
                'Exception thrown during command',
                $e->getMessage(),
                'file: ' . $e->getFile(),
                'line: ' . $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * @param array<string,string> $commands
     * @return void
     */
    public function load(array $commands): void
    {
        $this->command = array_merge($this->command, $commands);
    }

    public function getCommandNameFromServer(): string
    {
        return $_SERVER['argv'][0];
    }

    public function __serialize(): array
    {
        return $this->command;
    }

    public function __unserialize(array $data): void
    {
        $this->command = $data;
    }
}