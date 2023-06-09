<?php

namespace LiteApi\Test\Component\Config;

use LiteApi\Component\Config\ConfigLoader;
use LiteApi\Component\Config\Wrapper\ClassDefinitionWrapper;
use LiteApi\Test\resources\classes\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ConfigLoaderTest extends TestCase
{

    /**
     * @covers \LiteApi\Component\Config\ConfigLoader::getConfig
     */
    public function testGetConfig(): void
    {
        $projectDir = realpath(__DIR__ . '/../../resources/project/example1');
        $configLoader = new ConfigLoader($projectDir);
        $config = $configLoader->getConfig();
        $this->assertEquals($projectDir, $config->projectDir);
        $this->assertEquals([realpath(__DIR__ . '/../../resources/classes')], $config->servicesDir);
        $this->assertEquals([], $config->trustedIps);
        $this->assertEquals([Logger::class => []], $config->container);
        $this->assertEquals( new ClassDefinitionWrapper(
            [
                'class' => FilesystemAdapter::class,
                'args' => [
                'kernel', 0, $projectDir . '/var/cache']
            ]),
            $config->cache
        );
        $this->assertEquals('dev', $config->envParams->env);
        $this->assertEquals(true, $config->envParams->debug);
    }

}