<?php

namespace LiteApi\Http\Exception;

class MethodNotAllowedHttpException extends HttpException
{
    protected const CODE = 405;
}