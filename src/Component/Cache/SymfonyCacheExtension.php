<?php

namespace LiteApi\Component\Cache;

use LiteApi\Command\CommandsLoader;
use LiteApi\Component\Extension\Extension;
use LiteApi\Container\ContainerLoader;
use LiteApi\Container\Definition\AliasDefinition;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Container\Definition\Definition;
use Symfony\Contracts\Cache\CacheInterface;

class SymfonyCacheExtension extends Extension
{

    /*
     * id => [
     *     class => '',
     *     args => []
     * ]
     */

    public function registerServices(ContainerLoader $container): void
    {
        $setKernelCache = !$container->has('kernel.cache');
        /** @var array<string, Definition> $definitions */
        $definitions = [];
        foreach ($this->config as $id => $adapterConfig) {
            $definitions[$id] =  new ClassDefinition($adapterConfig['class'], $adapterConfig['args'] ?? []);
            if ($setKernelCache) {
                $definitions['kernel.cache'] = new AliasDefinition(CacheInterface::class, '@' . $id);
                $setKernelCache = false;
            }
        }
        $container->addDefinitions($definitions);
    }

    public function registerCommands(CommandsLoader $commandLoader): void
    {
        $commandLoader->registerCommand('cache:clear', SymfonyCacheClearCommand::class);
        $commandLoader->registerCommand('cache:warmup', SymfonyCacheWarmupCommand::class);
    }

}