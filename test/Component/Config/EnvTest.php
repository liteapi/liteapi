<?php

namespace LiteApi\Test\Component\Config;

use LiteApi\Component\Config\Env;
use LiteApi\Test\resources;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @covers \LiteApi\Component\Config\Env
 */
class EnvTest extends TestCase
{

    /**
     * @covers \LiteApi\Component\Config\Env::loadPhpConfig
     */
    public function testLoadConfigFile()
    {
        $env = new Env();
        $filename = __DIR__ . '/../../resources/config/base_config/env.php';
        $config = $env->loadPhpConfig($filename);
        $this->assertIsArray($config);
        $this->assertEquals(['env'=>'test', 'debug' => true], $config);
    }
}
