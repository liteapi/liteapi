<?php

namespace pjpawel\LightApi\Test\Route;

use pjpawel\LightApi\Container\ContainerLoader;
use pjpawel\LightApi\Route\Route;
use PHPUnit\Framework\TestCase;
use pjpawel\LightApi\Http\Request;
use pjpawel\LightApi\Test\resources\classes\ControllerOne;
use pjpawel\LightApi\Test\resources\classes\Logger;

/**
 * @covers \pjpawel\LightApi\Route\Route
 */
class RouteTest extends TestCase
{

    /**
     * @covers \pjpawel\LightApi\Route\Route
     */
    public function test__construct(): void
    {
        $endpoint = new Route(ControllerOne::class, 'index', '/index', []);
        $this->assertEquals('/index', $endpoint->path);
        $this->assertEquals([], $endpoint->httpMethods);
        $endpoint->makeRegexPath();
        $this->assertEquals('/^\/index$/', $endpoint->regexPath);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route
     */
    public function test__constructWithStringParam(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echo', '/echo/{identifier}', ['POST']);
        $this->assertEquals('/echo/{identifier}', $endpoint->path);
        $this->assertEquals(['POST'], $endpoint->httpMethods);
        $endpoint->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\w+)$/', $endpoint->regexPath);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route
     */
    public function test__constructWithIntParam(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echoInt', '/echo/{identifierInt}', ['GET']);
        $this->assertEquals('/echo/{identifierInt}', $endpoint->path);
        $this->assertEquals(['GET'], $endpoint->httpMethods);
        $endpoint->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\d+)$/', $endpoint->regexPath);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route
     */
    public function test__constructWithTwoParams(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echoTwoParams', '/echo/{channel}/list/{identifier}', ['POST', 'PUT']);
        $this->assertEquals('/echo/{channel}/list/{identifier}', $endpoint->path);
        $this->assertEquals(['POST', 'PUT'], $endpoint->httpMethods);
        $endpoint->makeRegexPath();
        $this->assertEquals('/^\/echo\/(\w+)\/list\/(\d+)$/', $endpoint->regexPath);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route::execute
     */
    public function testExecute(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echoInt', '/echo/{identifierInt}', ['GET']);
        $endpoint->makeRegexPath();
        $container = new ContainerLoader([Logger::class => []]);
        $request = new Request([], [], [], [], ['REQUESTED_METHOD' => 'GET', 'REQUEST_URI' => '/echo/12', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $endpoint->execute($container, $request);
        $this->assertEquals('echo12', $response->content);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route::execute
     */
    public function testExecuteWithTwoParams(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echoTwoParams', '/echo/{channel}/list/{identifier}', ['POST', 'PUT']);
        $endpoint->makeRegexPath();
        $container = new ContainerLoader([Logger::class => []]);
        $request = new Request([], [], [], [], ['REQUESTED_METHOD' => 'GET', 'REQUEST_URI' => '/echo/volvo/list/15', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $endpoint->execute($container, $request);
        $this->assertEquals('echo:volvo:15', $response->content);
    }

    /**
     * @covers \pjpawel\LightApi\Route\Route::execute
     */
    public function testExecuteWithQuery(): void
    {
        $endpoint = new Route(ControllerOne::class, 'echoQuery', '/echo/{identifier}', ['GET']);
        $endpoint->makeRegexPath();
        $container = new ContainerLoader([Logger::class => []]);
        $request = new Request(['id' => 19], [], [], [], ['REQUESTED_METHOD' => 'GET', 'REQUEST_URI' => '/echo/abc', 'REMOTE_ADDR' => '127.0.0.1']);
        $response = $endpoint->execute($container, $request);
        $this->assertEquals('requestId:19', $response->content);
    }


}
