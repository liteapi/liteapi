<?php

namespace LiteApi\Command\Internal;

use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Component\FilesManager;
use LiteApi\Component\Serializer;
use LiteApi\Kernel;
use ReflectionClass;

class CacheClearCommand extends KernelAwareCommand
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
        return self::SUCCESS;
    }
}