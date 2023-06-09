<?php

namespace LiteApi\Http\Exception;

use Exception;
use LiteApi\Exception\ProgrammerException;
use LiteApi\Http\ResponseStatus;
use Throwable;

class HttpException extends Exception
{

    /**
     * @param ResponseStatus $status
     * @param string $message
     * @param Throwable|null $previous
     * @throws ProgrammerException
     */
    public function __construct(public ResponseStatus $status, string $message = '', ?Throwable $previous = null)
    {
        if ($status->value < 400) {
            throw new ProgrammerException('HttpException must have status greater or equal to 400');
        }
        parent::__construct($message, $status->value, $previous);
    }

}