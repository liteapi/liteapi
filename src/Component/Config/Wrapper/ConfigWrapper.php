<?php

namespace LiteApi\Component\Config\Wrapper;

use LiteApi\Component\Util\ArrayWrapper;

class ConfigWrapper extends ArrayWrapper
{

    private const PROJECT_DIR = 'projectDir';
    private const TRUSTED_IPS = 'trustedIPs';
    private const SERVICES = 'services';
    private const CONTAINER = 'container';
    private const EXTENSIONS = 'extensions';
    private const CACHE = 'cache';

    public EnvWrapper $envParams;
    public string $projectDir;
    public array $trustedIps;
    public array $servicesDir;
    public array $container;
    public array $extensions;
    public ClassDefinitionWrapper $cache;

    public function __construct(EnvWrapper $env, array $config)
    {
        $this->envParams = $env;
        $config = $this->resolveEnvParams($config);
        parent::__construct($config);
    }

    protected function wrap(array $config): void
    {
        $this->assertHasKeys(
            $config,
            [
                self::PROJECT_DIR,
                self::TRUSTED_IPS,
                self::SERVICES,
                self::CONTAINER,
                self::EXTENSIONS,
                self::CACHE
            ]
        );

        $this->assertIsString($config[self::PROJECT_DIR]);
        $this->projectDir = $config[self::PROJECT_DIR];

        $trustedIps = $config[self::TRUSTED_IPS];
        $this->assertIsArray($trustedIps);
        $this->assertList($trustedIps);
        $this->trustedIps = $trustedIps;

        $services = $config[self::SERVICES];
        $this->assertIsArray($services);
        $this->assertList($services);
        $this->servicesDir = $services;

        $this->assertIsArray($config[self::CONTAINER]);
        $this->container = $config[self::CONTAINER];//new ContainerWrapper($config[self::CONTAINER]);

        $this->assertIsArray($config[self::EXTENSIONS]);
        $this->extensions = $config[self::EXTENSIONS];//new ExtensionsWrapper($config[self::EXTENSIONS]);

        $this->assertIsArray($config[self::CACHE]);
        $this->cache = new ClassDefinitionWrapper($config[self::CACHE]);
    }

    protected function resolveEnvParams(array $config): array
    {
        return $config;
        //TODO: make env params
//        $configJson = json_encode($config);
//        $pregResult = preg_match_all('/(%env\(.*\))/', $configJson, $matches);
//        var_dump($matches);
//        if ($pregResult > 0) {
//            foreach ($matches as $match) {
//
//            }
//        }
//        return json_decode($configJson, true);
    }

}