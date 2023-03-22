<?php

namespace LiteApi\Test\Container;

use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\InDirectDefinition;
use PHPUnit\Framework\TestCase;
use LiteApi\Component\Logger\SimpleLogger\SimpleLogger;
use LiteApi\Container\ContainerLoader;
use Psr\Log\LoggerInterface;

/**
 * @covers \LiteApi\Container\ContainerLoader
 */
class ContainerLoaderTest extends TestCase
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
     * @covers \LiteApi\Container\ContainerLoader::createDefinitionsFromConfig
     */
    public function test__construct(): void
    {
        $container = new ContainerLoader();
        $container->createDefinitionsFromConfig(self::CONFIG);
        $logger = $container->get(SimpleLogger::class);
        $this->assertLoggerIsSimpleLogger($logger);
        $logger = $container->get(LoggerInterface::class);
        $this->assertLoggerIsSimpleLogger($logger);
    }

    /**
     * @covers \LiteApi\Container\ContainerLoader::add
     */
    public function testAdd(): void
    {
        $config = [
            'name' => SimpleLogger::class,
            'args' => [
                __DIR__ . '/../../tmp/test.log'
            ]
        ];
        $container = new ContainerLoader([]);
        $container->add($config);
        $this->assertLoggerIsSimpleLogger($container->get(SimpleLogger::class));
    }

    /**
     * @covers \LiteApi\Container\ContainerLoader::addDefinitions
     */
    public function testAddDefinitions(): void
    {
        $container = new ContainerLoader();
        $container->createDefinitionsFromConfig(self::CONFIG);
        $definitions = $container->definitions;
        $container = new ContainerLoader();
        $container->addDefinitions($definitions);
        $this->assertLoggerIsSimpleLogger($container->get(SimpleLogger::class));
        $this->assertLoggerIsSimpleLogger($container->get(LoggerInterface::class));
    }

    /**
     * @covers \LiteApi\Container\ContainerLoader::getDefinitions
     */
    public function testGetDefinitions(): void
    {
        $container = new ContainerLoader();
        $container->createDefinitionsFromConfig(self::CONFIG);
        $definitions = $container->getDefinitions([
            SimpleLogger::class,
            LoggerInterface::class
        ]);
        $this->assertTrue(is_a($definitions[0],     ClassDefinition::class));
        $this->assertTrue(is_a($definitions[1], IndirectDefinition::class));
    }
}
