<?php

namespace LiteApi\Test\Route;

use LiteApi\Container\Container;
use LiteApi\Route\Route;
use PHPUnit\Framework\TestCase;
use LiteApi\Http\Request;
use LiteApi\Test\resources\classes\ControllerOne;
use LiteApi\Test\resources\classes\ControllerTwo;
use LiteApi\Test\resources\classes\Logger;

/**
 * @covers \LiteApi\Route\Route
 */
class RouteTest extends TestCase
{

    /**
     * @covers \LiteApi\Route\Route
     */
    public function test__construct(): void
    {
        $route = new Route(ControllerOne::class, 'index', '/index', []);
        $this->assertEquals('/index', $route->path);
        $this->assertEquals([], $route->httpMethods);
        $route->makeRegexPath();
        $this->assertEquals('/^\/index$/', $route->regexPath);
    }

    /**
     * @covers \LiteApi\Route\Route
     */
    public function test__constructWithStringParam(): void
    {
        $route = new Route(ControllerOne::class, 'echo', '/echo/{identifier}', ['POST']);
        $this->assertEquals('/echo/{identifier}', $route->path);
        $this->assertEquals(['POST'], $route->httpMethods);
        $route->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\w+)$/', $route->regexPath);
    }

    /**
     * @covers \LiteApi\Route\Route
     */
    public function test__constructWithIntParam(): void
    {
        $route = new Route(ControllerOne::class, 'echoInt', '/echo/{identifierInt}', ['GET']);
        $this->assertEquals('/echo/{identifierInt}', $route->path);
        $this->assertEquals(['GET'], $route->httpMethods);
        $route->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\d+)$/', $route->regexPath);
    }

    /**
     * @covers \LiteApi\Route\Route
     */
    public function test__constructWithTwoParams(): void
    {
        $route = new Route(ControllerOne::class, 'echoTwoParams', '/echo/{channel}/list/{identifier}', ['POST', 'PUT']);
        $this->assertEquals('/echo/{channel}/list/{identifier}', $route->path);
        $this->assertEquals(['POST', 'PUT'], $route->httpMethods);
        $route->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\w+)\/list\/(\d+)$/', $route->regexPath);
    }

    /**
     * @covers \LiteApi\Route\Route::execute
     */
    public function testExecute(): void
    {
        $route = new Route(ControllerOne::class, 'echoInt', '/echo/{identifierInt}', ['GET']);
        $route->makeRegexPath();
        $container = new Container();
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/echo/12', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $route->execute($container, $request);
        $this->assertEquals('echo12', $response->content);
    }

    /**
     * @covers \LiteApi\Route\Route::execute
     */
    public function testExecuteWithTwoParams(): void
    {
        $route = new Route(ControllerOne::class, 'echoTwoParams', '/echo/{channel}/list/{identifier}', ['POST', 'PUT']);
        $route->makeRegexPath();
        $container = new Container();
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/echo/volvo/list/15', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $route->execute($container, $request);
        $this->assertEquals('echo:volvo:15', $response->content);
    }

    /**
     * @covers \LiteApi\Route\Route::execute
     */
    public function testExecuteWithQuery(): void
    {
        $route = new Route(ControllerOne::class, 'echoQuery', '/echo/{identifier}', ['GET']);
        $route->makeRegexPath();
        $container = new Container();
        $request = new Request(['id' => 19], server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/echo/abc', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $route->execute($container, $request);
        $this->assertEquals('requestId:19', $response->content);
    }

    /**
     * @covers \LiteApi\Route\Route::execute
     */
    public function testExecuteWithContainerServices(): void
    {
        $route = new Route(ControllerTwo::class, 'index', '/index', ['GET']);
        $route->makeRegexPath();
        $container = new Container();
        $container->add(['name' => Logger::class]);
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $route->execute($container, $request);
        $this->assertEquals('tellOne', $response->content);
    }
}
