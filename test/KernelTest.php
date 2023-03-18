<?php

namespace LiteApi\Test;

use PHPUnit\Framework\TestCase;
use LiteApi\Container\ContainerLoader;
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
        //$config = Env::getConfigFromEnv($configDir);
        return new Kernel($configDir);
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
        $reflectionClass = new ReflectionClass(Kernel::class);
        /** @var ContainerLoader $container */
        $container = $reflectionClass->getProperty('containerLoader')->getValue($kernel);
        $this->assertTrue($container->has(Logger::class));
        /** @var Logger $logger */
        $logger = $container->get(Logger::class);
        $this->assertTrue($logger instanceof Logger);
        $this->assertEquals('tellOne', $logger->tellOne());
    }
}
