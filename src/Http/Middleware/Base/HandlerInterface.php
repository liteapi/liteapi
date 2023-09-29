<?php

namespace LiteApi\Http\Middleware\Base;

use LiteApi\Http\Request\Request;
use LiteApi\Http\Route;

interface HandlerInterface
{

    public function handle(Request $request, Route $route): void;

}