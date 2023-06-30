<?php

namespace LiteApi\Route\Attribute;

use Attribute;
use LiteApi\Route\QueryType;

/**
 * Query key that IF EXISTS in request will be parsed as given definition says
 */
#[Attribute(Attribute::TARGET_METHOD| Attribute::TARGET_FUNCTION)]
class HasQuery
{

    /**
     * @param string $key
     * @param string|QueryType $type
     */
    public function __construct(
        public string $key,
        public string|QueryType $type
    )
    {
    }

}