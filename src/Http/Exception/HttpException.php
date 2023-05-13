<?php

namespace LiteApi\Http\Exception;

use Exception;
use Throwable;

class HttpException extends Exception
{

    protected const CODE = null;

    public function __construct(?int $code = null, string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, $code ?? self::CODE, $previous);
    }

}