<?php

namespace LiteApi\Component\Loader;


class DefinitionsTransfer
{

    public function __construct(
        /** @var array<string,\LiteApi\Container\Definition\Definition> */
        public array $services,
        /** @var array<string,string> */
        public array $commands,
        /** @var \LiteApi\Route\Route[] */
        public array $routes,
        /** @var array<int,string> */
        public array $onErrors,
    )
    {
    }

}