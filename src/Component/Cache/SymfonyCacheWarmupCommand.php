<?php

namespace LiteApi\Component\Cache;

use LiteApi\Command\Command;
use LiteApi\Command\Input\InputInterface;
use LiteApi\Command\Output\OutputInterface;
use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Kernel;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class SymfonyCacheWarmupCommand extends Command implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    protected function kernelCache(): AbstractAdapter
    {
        return $this->container->get(Kernel::KERNEL_CACHE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->kernelCache()->reset();
        return self::SUCCESS;
    }
}