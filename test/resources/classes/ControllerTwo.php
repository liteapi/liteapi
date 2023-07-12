<?php

namespace LiteApi\Test\resources\classes;

use LiteApi\Container\Awareness\ContainerAwareInterface;
use LiteApi\Container\Awareness\ContainerAwareTrait;
use LiteApi\Http\Request\Attribute\AsRoute;
use LiteApi\Http\Response\Response;

class ControllerTwo implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    protected function logger(): Logger
    {
        return $this->container->get(Logger::class);
    }

    #[AsRoute('/index')]
    public function index(): Response
    {
        return new Response($this->logger()->tellOne());
    }

}