<?php

namespace LiteApi\Route;

use Exception;
use LiteApi\Container\Container;
use LiteApi\Exception\ProgrammerException;
use LiteApi\Http\Exception\HttpException;
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
                }
                break;
            }
        }
        if (!isset($matchedRoute)) {
            throw new HttpException($methodNotAllowed ? ResponseStatus::MethodNotAllowed : ResponseStatus::NotFound);
        }
        return $matchedRoute;
    }

    public function executeRoute(Route $route, Container $container, Request $request): Response
    {
        try {
            return $route->execute($container, $request);
        } catch (HttpException $httpException) {
            if (isset($this->onError[$httpException->status->value])) {
                return $this->onError[$httpException->status->value]($httpException);
            }
            return new Response($httpException->getMessage(), $httpException->status);
        } catch (Exception $e) {
            $internalError = ResponseStatus::InternalServerError;
            if (isset($this->onError[$internalError->value])) {
                return $this->onError[$internalError->value]($e);
            }
            return new Response($internalError->getText(), $internalError);
        }
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
}