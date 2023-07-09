<?php

namespace LiteApi\Test;

use LiteApi\Component\Config\ConfigLoader;
use LiteApi\Component\Config\Env;
use PHPUnit\Framework\TestCase;
use LiteApi\Container\Container;
use LiteApi\Kernel;
use LiteApi\Test\resources\classes\Logger;
use ReflectionClass;

/**
 * @covers \LiteApi\Kernel
 */
class KernelTest extends TestCase
{

    private function createKernel(): Kernel
    {
        $configDir = __DIR__ . '/resources/config/base_config';
        $configLoader = new ConfigLoader($configDir);
        $configLoader->loadConfig();
        $config = $configLoader->getConfig();
        return new Kernel($config, $configLoader->getCache());
    }

    /**
     * @covers \LiteApi\Kernel::__construct
     */
    public function test__construct(): void
    {
        $kernel = $this->createKernel();
        $this->assertTrue(is_object($kernel));
    }

    /**
     * @covers \LiteApi\Kernel::boot
     */
    public function testBoot(): void
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->assertTrue($container->has(Logger::class));
        /** @var Logger $logger */
        $logger = $container->get(Logger::class);
        $this->assertTrue($logger instanceof Logger);
        $this->assertEquals('tellOne', $logger->tellOne());
    }
}
