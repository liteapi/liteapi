<?php

namespace LiteApi\Http\ExceptionHandler;

use Exception;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\ExceptionHandler\Base\ExceptionHandlerInterface;
use LiteApi\Http\Response\Response;
use LiteApi\Http\Response\ResponseStatus;

class DefaultHandler implements ExceptionHandlerInterface
{

    public function handle(Exception $exception): Exception|Response
    {
        if ($exception instanceof HttpException) {
            return $exception->createResponse();
        } else {
            return new Response('Internal server error occurred', ResponseStatus::InternalServerError);
        }
    }

}