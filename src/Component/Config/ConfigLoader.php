<?php

namespace LiteApi\Component\Config;

use Exception;
use LiteApi\Component\Common\BuiltinValue;
use LiteApi\Component\Config\Wrapper\ClassDefinitionWrapper;
use LiteApi\Component\Config\Wrapper\ConfigWrapper;
use LiteApi\Component\Config\Wrapper\EnvWrapper;
use LiteApi\Exception\KernelException;
use LiteApi\Exception\ProgrammerException;
use Psr\Cache\CacheItemPoolInterface;

class ConfigLoader
{

    private const CACHE_CONFIG_FILE = DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'cache.php';
    private const CONFIG_FILE = DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
    private const CACHE_SERIALIZE_KEY = 'kernel.config';

    private EnvWrapper $envWrapper;
    private ConfigWrapper $config;
    private CacheItemPoolInterface $cache;

    public function __construct(
        private readonly string $projectDir
    )
    {
    }

    public function loadConfig(bool $useCache = true): void
    {
        Env::load($this->projectDir);
        $envName = 'APP_ENV';
        $envDebug = 'APP_DEBUG';
        $this->envWrapper = new EnvWrapper([$envName => Env::getValue($envName), $envDebug => Env::getValue($envDebug)]);
        $cacheConfigFile = $this->projectDir . self::CACHE_CONFIG_FILE;
        if (file_exists($cacheConfigFile) === false) {
            throw new Exception('Missing cache config file');
        }
        $cacheConfig = require $cacheConfigFile;
        $this->cache = (new ClassDefinitionWrapper($cacheConfig))->createObject();
        if ($useCache === true) {
            $serializeCacheItem = $this->cache->getItem(self::CACHE_SERIALIZE_KEY);
            if ($serializeCacheItem->isHit()) {
                $this->config = $serializeCacheItem->get();
                return;
            }
        }
        $configArray = require $this->projectDir . self::CONFIG_FILE;
        $this->resolveEnvParams($configArray);
        $this->config = new ConfigWrapper($configArray, $this->envWrapper);
        if ($useCache === true) {
            $serializeCacheItem = $this->cache->getItem(self::CACHE_SERIALIZE_KEY);
            $serializeCacheItem->set($this->config);
        }
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
            function(&$item): void {
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
            return $type !== null ? $type->convertValue($value) : $value;
        }
        return Env::getValue($name, $type);
    }

    public function getConfig(): ConfigWrapper
    {
        return $this->config;
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }

}