<?php

namespace LiteApi\Http\Request\Attribute;

use LiteApi\Http\Response\ResponseStatus;

#[\Attribute(\Attribute::TARGET_METHOD)]
class OnError
{

    public function __construct(
        public ResponseStatus $status
    )
    {
    }

}