<?php

namespace LiteApi\Test\Container;

use LiteApi\Test\resources\classes\SimpleLogger;
use PHPUnit\Framework\TestCase;
use LiteApi\Container\Container;
use Psr\Log\LoggerInterface;

/**
 * @covers \LiteApi\Container\Container
 */
class ContainerTest extends TestCase
{

    public const CONFIG = [
        SimpleLogger::class => [
            __DIR__ . '/../../tmp/test.log'
        ],
        LoggerInterface::class => '@' . SimpleLogger::class
    ];

    private function assertLoggerIsSimpleLogger($logger): void
    {
        $this->assertTrue(is_subclass_of($logger, LoggerInterface::class));
        $this->assertTrue(is_a($logger, SimpleLogger::class));
    }

    /**
     * @covers \LiteApi\Container\Container::createDefinitionsFromConfig
     */
    public function test__construct(): void
    {
        $container = new Container();
        $container->createDefinitionsFromConfig(self::CONFIG);
        $logger = $container->get(SimpleLogger::class);
        $this->assertLoggerIsSimpleLogger($logger);
        $logger = $container->get(LoggerInterface::class);
        $this->assertLoggerIsSimpleLogger($logger);
    }

    /**
     * @covers \LiteApi\Container\Container::add
     */
    public function testAdd(): void
    {
        $config = [
            'name' => SimpleLogger::class,
            'args' => [
                __DIR__ . '/../../tmp/test.log'
            ]
        ];
        $container = new Container();
        $container->add($config);
        $this->assertLoggerIsSimpleLogger($container->get(SimpleLogger::class));
    }

    /**
     * @covers \LiteApi\Container\Container::addDefinitions
     */
    public function testAddDefinitions(): void
    {
        $container = new Container();
        $container->createDefinitionsFromConfig(self::CONFIG);
        $definitions = $container->definitions;
        $container = new Container();
        $container->addDefinitions($definitions);
        $this->assertLoggerIsSimpleLogger($container->get(SimpleLogger::class));
        $this->assertLoggerIsSimpleLogger($container->get(LoggerInterface::class));
    }
}
