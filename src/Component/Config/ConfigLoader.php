<?php

namespace LiteApi\Component\Config;

use LiteApi\Component\Config\Wrapper\ConfigWrapper;

class ConfigLoader
{

    private const CACHE_SERIALIZED_PATH = '/var/cache/config';

    public function __construct(
        private readonly string $projectDir
    )
    {
    }

    public function getConfig(): ConfigWrapper
    {
        $serializedConfigPath = $this->projectDir . self::CACHE_SERIALIZED_PATH;
        if (file_exists($serializedConfigPath)) {
            $lastModified = filemtime($serializedConfigPath);
            if ($lastModified !== false && $lastModified + 360 > time()) {
                return unserialize(file_get_contents($serializedConfigPath));
            }
        }
        $env = new Env();
        $envWrapper = $env->getEnvParams($this->projectDir);
        $configArray = require $this->projectDir . '/config/config.php';
        $config = new ConfigWrapper($envWrapper, $configArray);
        if (!$config->envParams->debug) {
            $this->serializeConfig($config);
        }
        return $config;
    }

    private function serializeConfig(ConfigWrapper $config): void
    {
        file_put_contents(
            $this->projectDir . self::CACHE_SERIALIZED_PATH,
            serialize($config)
        );
    }

}