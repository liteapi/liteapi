<?php

namespace LiteApi\Component\Loader;

use LiteApi\Component\Util\FilesManager;
use LiteApi\Container\Definition\ClassDefinition;
use LiteApi\Exception\Trigger;
use Psr\Cache\CacheItemPoolInterface;

class DefinitionLoader
{

    public function loadFromComposer(string $projectDir, CacheItemPoolInterface $cache): DefinitionsTransfer
    {
        $composerLockPath = $projectDir . DIRECTORY_SEPARATOR . 'composer.lock';
        $serviceComposerTimeCache = $cache->getItem('kernel.definitions.composer.time');
        $composerServicesLoaded = false;
        if ($serviceComposerTimeCache->isHit() && filemtime($composerLockPath) < $serviceComposerTimeCache->get()) {
            $serviceComposerCache = $cache->getItem('kernel.definitions.composer');
            if ($serviceComposerCache->isHit()) {
                /** @var DefinitionsTransfer $definitions */
                $definitions = $serviceComposerCache->get();
                return $definitions;
            }
        }
        $autoloadPs4Path = $projectDir . DIRECTORY_SEPARATOR . 'vendor/composer/autoload_psr4.php';
        if (!file_exists($autoloadPs4Path)) {
            Trigger::warn('Cannot read autoload_ps4.php file in path ' . $autoloadPs4Path);
        }
        $autoloadPs4 = require $autoloadPs4Path;
        $filesManager = new FilesManager();
        $services = [];
        foreach ($autoloadPs4 as $namespace => $path) {
            $classes = $filesManager->getClassesNamesFromPath($path, $namespace);
            foreach ($classes as $class) {
                $services[$class] = new ClassDefinition($class, []);
            }
        }
        return new DefinitionsTransfer($services, [], [], []);
    }

}