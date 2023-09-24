<?php

namespace LiteApi\Http;

use Exception;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\Request\Request;
use LiteApi\Http\Response\Response;
use LiteApi\Http\Response\ResponseStatus;

class Router
{

    /**
     * @var Route[]
     */
    public array $routes = [];

    /**
     * @var array<int,string> key - http status, value method name
     */
    public array $onError = [];

    public function __construct(
        public array $trustedIps = []
    )
    {
    }

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
                    break;
                }
            }
        }
        if (!isset($matchedRoute)) {
            throw new HttpException($methodNotAllowed ? ResponseStatus::MethodNotAllowed : ResponseStatus::NotFound);
        }
        return $matchedRoute;
    }

    /**
     * @param string $ip
     * @return void
     * @throws HttpException
     */
    public function validateIp(string $ip): void
    {
        if (empty($this->trustedIps)) {
            return;
        }
        if (!in_array($ip, $this->trustedIps)) {
            throw new HttpException(ResponseStatus::Forbidden);
        }
    }

    public function getErrorResponse(Exception|HttpException $exception): Response
    {
        if ($exception instanceof HttpException) {
            return new Response($exception->getMessage(), $exception->status);
        } else {
            return new Response('Internal server error occurred', ResponseStatus::InternalServerError);
        }
    }

    public function registerOnError(int $statusCode, string $methodName): void
    {
        $this->onError[$statusCode] = $methodName;
    }

    /**
     * @param Route[] $routes
     * @return void
     */
    public function load(array $routes): void
    {
        foreach ($routes as $route) {
            $route->makeRegexPath();
        }
        $this->routes = array_merge($this->routes, $routes);
    }

    /**
     * @param array<int,string> $onErrors
     * @return void
     */
    public function loadOnError(array $onErrors): void
    {
        $this->onError = array_merge($this->onError, $onErrors);
    }
}