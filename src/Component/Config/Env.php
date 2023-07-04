<?php

namespace LiteApi\Component\Config;

use Exception;
use LiteApi\Component\Common\BuiltinValue;

class Env
{

    public const ENV_FILES = [
        '.env',
        '.env.local'
    ];

    public static function load(string $dir): void
    {
        if (!str_ends_with($dir, DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        $phpConfig = $dir . '.env.local.php';
        if (is_file($phpConfig)) {
            $params = self::loadPhpConfig($phpConfig);
        } else {
            $params = [];
            foreach (self::ENV_FILES as $filePath) {
                $fullFilePath = $dir . $filePath;
                if (file_exists($fullFilePath)) {
                    $params = array_merge($params, parse_ini_file($fullFilePath));
                }
            }
        }
        foreach ($params as $name => $value) {
            self::setValue($name, $value);
        }
    }

    /**
     * @param string $name
     * @param BuiltinValue|null $type
     * @return mixed
     * @throws Exception
     */
    public static function getValue(string $name, ?BuiltinValue $type = null): mixed
    {
        if (isset($_ENV[$name])) {
            $value = $_ENV[$name];
            return $type !== null ? $type->convertValue($value) : $value;
        }
        if (isset($_SERVER[$name])) {
            $value = $_SERVER[$name];
            return $type !== null ? $type->convertValue($value) : $value;
        }
        throw new Exception("$name wasn't found in env variables");
    }

    public static function isSet(string $name): bool
    {
        if (isset($_ENV[$name]) || isset($_SERVER[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Sets value only if overwrite is true or $name not exists in $_ENV or $_SERVER
     *
     * @param string $name
     * @param mixed $value
     * @param bool $overwrite
     * @return void
     */
    public static function setValue(string $name, mixed $value, bool $overwrite = false): void
    {
        if ((!isset($_ENV[$name]) && !isset($_SERVER[$name])) || $overwrite) {
            $_ENV[$name] = $value;
        }
    }

    /**
     * Method to get configuration array from a php file
     *
     * @param string $filePath
     * @return array
     */
    public static function loadPhpConfig(string $filePath): array
    {
        return require $filePath;
    }

}