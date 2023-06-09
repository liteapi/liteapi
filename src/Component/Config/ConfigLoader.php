<?php

namespace LiteApi\Component\Config;

use LiteApi\Component\Config\Wrapper\ConfigWrapper;

class ConfigLoader
{

    public function __construct(
        private readonly string $projectDir
    )
    {
    }

    public function getConfig(): ConfigWrapper
    {
        $serializedConfigPath = $this->projectDir . '/var/cache/config';
        if (file_exists($serializedConfigPath)) {
            $lastModified = filemtime($serializedConfigPath);
            if ($lastModified !== false && $lastModified + 360 > time()) {
                return unserialize(file_get_contents($serializedConfigPath));
            }
        }
        $env = new Env();
        $envWrapper = $env->getEnvParams($this->projectDir);
        $configArray = require $this->projectDir . '/config/config.php';
        return new ConfigWrapper($envWrapper, $configArray);
    }

}