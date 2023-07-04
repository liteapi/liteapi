<?php

namespace LiteApi\Component\Config\Wrapper;

use LiteApi\Component\Common\ArrayWrapper;

class ConfigWrapper extends ArrayWrapper
{

    private const PROJECT_DIR = 'projectDir';
    private const TRUSTED_IPS = 'trustedIPs';
    private const SERVICES = 'services';
    private const CONTAINER = 'container';
    private const EXTENSIONS = 'extensions';
    private const CACHE = 'cache';
    private const KERNEL_SUBSCRIBER = 'kernelSubscriber';

    public EnvWrapper $envParams;
    public string $projectDir;
    /** @var string[] */
    public array $trustedIps;
    /** @var string[] */
    public array $servicesDir;
    public array $container;
    public array $extensions;
    public ClassDefinitionWrapper $cache;
    public ?string $kernelSubscriber = null;

    public function __construct(array $config, EnvWrapper $envWrapper)
    {
        $this->envParams = $envWrapper;
        parent::__construct($config);
    }

    protected function wrap(array $config): void
    {
        $this->assertHasOnlyPermittedKeys(
            $config,
            [
                self::PROJECT_DIR,
                self::TRUSTED_IPS,
                self::SERVICES,
                self::CONTAINER,
                self::EXTENSIONS,
                self::CACHE,
                self::KERNEL_SUBSCRIBER
            ]
        );
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

        if (isset($config[self::KERNEL_SUBSCRIBER])) {
            $kernelSubscriber = $config[self::KERNEL_SUBSCRIBER];
            $this->assertIsString($kernelSubscriber);
            $this->kernelSubscriber = $kernelSubscriber;
        }
    }
}