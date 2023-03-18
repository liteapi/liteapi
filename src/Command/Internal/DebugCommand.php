<?php

namespace LiteApi\Command\Internal;

use Exception;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Kernel;
use ReflectionClass;

class DebugCommand extends KernelAwareCommand
{

    private const COMPONENTS = [
        'container' => 'containerLoader',
        'router' => 'router',
        'commands' => 'commandLoader'
    ];

    public function prepare(InputInterface $input): void
    {
        $input->addArgument('component', description: 'Component to get info about. There is container, router, command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $component = $input->getArgument('component');
        if (!array_key_exists($component, self::COMPONENTS)) {
            throw new Exception('Invalid component');
        }
        $reflectionClass = new ReflectionClass(Kernel::class);
        $reflectionProperty = $reflectionClass->getProperty(self::COMPONENTS[$component]);
        $value = $reflectionProperty->getValue($this->kernel);
        $toShow = match ($component) {
            'container' => $value->definitions,
            'router' => $value->routes,
            'commands' => $value->command
        };
        $output->write(var_export($toShow, true));
        return self::SUCCESS;
    }
}