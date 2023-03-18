<?php

namespace LiteApi\Http\Exception;

class NotFoundHttpException extends HttpException
{
    protected const CODE = 404;
}