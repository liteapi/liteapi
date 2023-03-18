<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\CommandsLoader;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Component\FilesManager;
use LiteApi\Component\Serializer;
use LiteApi\Container\ContainerLoader;
use LiteApi\Kernel;
use LiteApi\Route\Router;
use ReflectionClass;

class WarmUpCacheCommand extends KernelAwareCommand
{

    private FilesManager $filesManager;

    public function __construct()
    {
        $this->filesManager = new FilesManager();
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $reflectionClass = new ReflectionClass(Kernel::class);
        /** @var Serializer $serializer */
        $serializer = $reflectionClass->getProperty('serializer')->getValue($this->kernel);
        $serializerClass = new ReflectionClass($serializer);
        $serializerDir = $serializerClass->getProperty('serializedDir')->getValue($serializer);
        if (is_dir($serializerDir)) {
            $this->filesManager->removeDirRecursive($serializerDir);
        }
        $serializer->makeSerialization([
            ContainerLoader::class => $reflectionClass->getProperty('containerLoader')->getValue($this->kernel),
            Router::class => $reflectionClass->getProperty('router')->getValue($this->kernel),
            CommandsLoader::class => $reflectionClass->getProperty('commandLoader')->getValue($this->kernel)
        ]);
        return self::SUCCESS;
    }
}