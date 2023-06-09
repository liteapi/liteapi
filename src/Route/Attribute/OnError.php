<?php

namespace LiteApi\Route\Attribute;

use LiteApi\Http\ResponseStatus;

#[\Attribute(\Attribute::TARGET_METHOD)]
class OnError
{

    public function __construct(
        public ResponseStatus $status
    )
    {
    }

}