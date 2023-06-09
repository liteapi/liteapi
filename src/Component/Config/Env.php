<?php

namespace LiteApi\Component\Config;

use LiteApi\Component\Config\Wrapper\EnvWrapper;

class Env
{

    public const ENV_FILES = [
        '.env',
        '.env.local'
    ];

    public function getEnvParams(string $dir): EnvWrapper
    {
        if (!str_ends_with($dir, DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        $phpConfig = $dir . '.env.local.php';
        if (is_file($phpConfig)) {
            return new EnvWrapper($this->loadPhpConfig($phpConfig));
        }

        $params = [];
        foreach (self::ENV_FILES as $filePath) {
            $fullFilePath = $dir . $filePath;
            if (file_exists($fullFilePath)) {
                $params = array_merge($params, parse_ini_file($fullFilePath));
            }
        }
        return new EnvWrapper($params);
    }

    /**
     * Method to get configuration array form a file
     *
     * @param string $filePath
     * @return array
     */
    public function loadPhpConfig(string $filePath): array
    {
        return require $filePath;
    }

}