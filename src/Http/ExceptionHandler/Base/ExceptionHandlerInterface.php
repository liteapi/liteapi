<?php

namespace LiteApi\Http\ExceptionHandler\Base;

use Exception;
use LiteApi\Http\Response\Response;

interface ExceptionHandlerInterface
{

    public function handle(Exception $exception): Exception | Response;

}