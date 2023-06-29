<?php

namespace LiteApi\Component\Cache\Command;

use LiteApi\Command\CommandsLoader;
use LiteApi\Component\Extension\Extension;
use LiteApi\Container\Container;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\Definition;
use LiteApi\Container\Definition\InDirectDefinition;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Must be moved to liteapi/symfony-extension
 */
class SymfonyCacheExtension extends Extension
{

    public function registerServices(Container $container): void
    {
        $setKernelCache = !$container->has('kernel.cache');
        /** @var array<string, Definition> $definitions */
        $definitions = [];
        foreach ($this->config as $id => $adapterConfig) {
            $definitions[$id] =  new ClassDefinition($adapterConfig['class'], $adapterConfig['args'] ?? []);
            if ($setKernelCache) {
                $definitions['kernel.cache'] = new InDirectDefinition($id);
                $definitions[CacheInterface::class] = new InDirectDefinition($id);
                $setKernelCache = false;
            }
        }
        $container->load($definitions);
    }

    public function registerCommands(CommandsLoader $commandLoader): void
    {
    }

}