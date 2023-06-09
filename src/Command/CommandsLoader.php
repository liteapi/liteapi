<?php

namespace LiteApi\Command;

use Exception;
use LiteApi\Command\Input\Stdin;
use LiteApi\Command\Internal\CacheClearCommand;
use LiteApi\Command\Internal\DebugCommandLoaderCommand;
use LiteApi\Command\Internal\DebugContainerCommand;
use LiteApi\Command\Internal\DebugCommand;
use LiteApi\Command\Internal\DebugRouterCommand;
use LiteApi\Command\Internal\KernelAwareCommand;
use LiteApi\Command\Internal\WarmUpCacheCommand;
use LiteApi\Command\Output\Stdout;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\ContainerLoader;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Kernel;
use ReflectionClass;
use ReflectionNamedType;

class CommandsLoader
{

    private const KERNEL_COMMANDS = [
        'debug:all' => DebugCommand::class,
        'cache:warmup' => WarmUpCacheCommand::class,
        'cache:clear' => CacheClearCommand::class,
        'debug:container' => DebugContainerCommand::class,
        'debug:router' => DebugRouterCommand::class,
        'debug:command' => DebugCommandLoaderCommand::class
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

    /**
     * @param string $commandName
     * @param ContainerLoader $container
     * @param null|Kernel $kernel
     * @return int
     */
    public function runCommandFromName(string $commandName, ContainerLoader $container, ?Kernel $kernel = null): int
    {
        $stdout = new Stdout();
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
            $stdin = new Stdin();
            /** @var Command $command */
            $command = $reflectionClass->newInstanceArgs($args);
            $command->prepare($stdin);
            /* Prepare input */
            $stdin->load();
            /* Inject services */
            if (is_subclass_of($command, ContainerAwareInterface::class)) {
                $command->setContainer($container);
            }
            /* If $command is KernelAwareCommand set Kernel */
            if ($kernel != null && is_subclass_of($command, KernelAwareCommand::class)) {
                $command->setKernel($kernel);
            }
            return $command->execute($stdin, $stdout);
        } catch (Exception $e) {
            $stdout->writeln([
                'Exception thrown during command',
                $e->getMessage(),
                'file: ' . $e->getFile(),
                'line: ' . $e->getLine()
            ]);
            return Command::FAILURE;
        }
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