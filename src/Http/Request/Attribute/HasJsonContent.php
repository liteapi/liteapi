<?php

namespace LiteApi\Http\Request\Attribute;

use Attribute;

/**
 * Content will be converted to associative array
 * and check if all required keys exists
 */
#[Attribute(Attribute::TARGET_METHOD| Attribute::TARGET_FUNCTION)]
class HasJsonContent
{

    /**
     * @param string[] $requiredParams List of required key values in json
     */
    public function __construct(
        public array $requiredParams
    )
    {
    }

}