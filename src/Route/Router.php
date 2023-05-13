<?php

namespace LiteApi\Route;

use Exception;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\Exception\MethodNotAllowedHttpException;
use LiteApi\Http\Exception\NotFoundHttpException;
use LiteApi\Http\Request;
use LiteApi\Http\Response;
use LiteApi\Http\ResponseStatus;

class Router
{

    /**
     * @var Route[]
     */
    public array $routes = [];

    /**
     * Register new route
     *
     * @param string $className
     * @param string $methodName
     * @param string $path
     * @param array $httpMethods
     * @return void
     * @throws \ReflectionException
     */
    public function registerRoute(string $className, string $methodName, string $path, array $httpMethods): void
    {
        $route = new Route($className, $methodName, $path, $httpMethods);
        $route->makeRegexPath();
        $this->routes[] = $route;
    }

    /**
     * @param Request $request
     * @return Route
     * @throws HttpException
     */
    public function getRoute(Request $request): Route
    {
        $methodNotAllowed = false;
        foreach ($this->routes as $route) {
            if (preg_match($route->regexPath, $request->path) === 1) {
                if (!empty($route->httpMethods) && !in_array($request->method, $route->httpMethods)) {
                    $methodNotAllowed = true;
                } else {
                    $matchedRoute = $route;
                }
                break;
            }
        }
        if (!isset($matchedRoute)) {
            throw new HttpException($methodNotAllowed ? ResponseStatus::METHOD_NOT_ALLOWED : ResponseStatus::NOT_FOUND);
        }
        return $matchedRoute;
    }

    public function getErrorResponse(Exception|HttpException $exception): Response
    {
        if ($exception instanceof HttpException) {
            return new Response($exception->getMessage(), $exception->status);
        } else {
            return new Response('Internal server error occurred', ResponseStatus::INTERNAL_SERVER_ERROR);
        }
    }
}