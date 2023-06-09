<?php

namespace LiteApi\Route\Attribute;

use Attribute;
use LiteApi\Route\QueryType;

/**
 * Query that IF EXISTS in request will be parsed as given definition says
 */
#[Attribute(Attribute::TARGET_METHOD| Attribute::TARGET_FUNCTION)]
class HasQuery
{

    /**
     * @param array<string,QueryType|string> $query Array containing name of query written as key and parsing type as value
     */
    public function __construct(
        public array $query
    )
    {
    }

}