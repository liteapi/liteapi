<?php

namespace LiteApi\Component\Config;

use LiteApi\Component\Common\BuiltinValue;
use LiteApi\Component\Config\Wrapper\ConfigWrapper;
use LiteApi\Component\Config\Wrapper\EnvWrapper;
use LiteApi\Exception\KernelException;
use LiteApi\Exception\ProgrammerException;

class ConfigLoader
{

    private const CACHE_SERIALIZED_PATH = '/var/cache/config';

    private EnvWrapper $envWrapper;

    public function __construct(
        private readonly string $projectDir
    )
    {
    }

    public function getConfig(): ConfigWrapper
    {
        $serializedConfigPath = $this->projectDir . self::CACHE_SERIALIZED_PATH; //TODO: different path?
        if (file_exists($serializedConfigPath)) {
            $lastModified = filemtime($serializedConfigPath);
            if ($lastModified !== false && $lastModified + 360 > time()) {
                return unserialize(file_get_contents($serializedConfigPath));
            }
        }
        Env::load($this->projectDir);
        $this->envWrapper = new EnvWrapper(['APP_ENV'=> Env::getValue('APP_ENV'), 'APP_DEBUG' => Env::getValue('APP_DEBUG')]);
        $configArray = require $this->projectDir . '/config/config.php';
        $this->resolveEnvParams($configArray);
        $config = new ConfigWrapper($configArray, $this->envWrapper);
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

    /**
     * Method to search for values needs to inject env values
     *
     * @param array $config
     * @return void
     * @throws KernelException|ProgrammerException
     */
    protected function resolveEnvParams(array &$config): void
    {
        $result = array_walk_recursive(
            $config,
            function (&$item): void {
                if (is_string($item)) {
                    if (str_contains($item, '%env')) {
                        $pregResult = preg_match('/%(?<env>env)\(((?<type>\w+):)?(?<name>\w+)\)%/', $item, $matches);
                        if ($pregResult > 0) {
                            $type = $matches['type'] ?? null;
                            $name = $matches['name'];
                            $value = $this->getEnvParam($name, $type);
                            $item = str_replace($matches[0], $value, $item);
                        }
                    } elseif (str_contains($item, '%project_dir%')) {
                        $item = str_replace('%project_dir%', $this->projectDir, $item);
                    }
                }
            }
        );
        if ($result === false) {
            throw new KernelException('Cannot resolve env params');
        }
    }

    /**
     * @param string $name
     * @param string|null $type
     * @return mixed
     * @throws ProgrammerException
     */
    private function getEnvParam(string $name, ?string $type = null): mixed
    {
        if ($type !== null) {
            $requestedType = $type;
            $type = BuiltinValue::tryFrom($requestedType);
            if ($type === null) {
                throw new ProgrammerException(sprintf('Cannot find type: %s to parse env value', $requestedType));
            }
        }
        if (isset($this->envWrapper->params[$name])) {
            $value = $this->envWrapper->params[$name];
            return $type !==  null ? $type->convertValue($value) : $value;
        }
        return Env::getValue($name, $type);
    }

}