<?php

namespace LiteApi\Test\Http;

use Exception;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\Request\Request;
use LiteApi\Http\Response\ResponseStatus;
use LiteApi\Test\resources\classes\ControllerOne;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LiteApi\Http\Router
 */
class RouterTest extends TestCase
{

    /**
     * @covers \LiteApi\Http\Router::getErrorResponse
     */
    public function testGetErrorResponse(): void
    {
        $router = new \LiteApi\Http\Router();
        $exception = new Exception('Something wrong happened');
        $response = $router->getErrorResponse($exception);
        $this->assertEquals('Internal server error occurred', $response->content);
        $this->assertEquals(ResponseStatus::InternalServerError, $response->status);
        $exception = new HttpException(ResponseStatus::MethodNotAllowed, 'Something wrong happened');
        $response = $router->getErrorResponse($exception);
        $this->assertEquals('Something wrong happened', $response->content);
        $this->assertEquals(ResponseStatus::MethodNotAllowed, $response->status);
    }

    /**
     * @covers \LiteApi\Http\Router::getRoute
     * @covers \LiteApi\Http\Router::registerRoute
     */
    public function testGetRoute(): void
    {
        $router = new \LiteApi\Http\Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', []);
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof \LiteApi\Http\Route);
    }

    /**
     * @covers \LiteApi\Http\Router::getRoute
     * @covers \LiteApi\Http\Router::registerRoute
     */
    public function testGetEmptyRoute(): void
    {
        $router = new \LiteApi\Http\Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', []);
        $router->registerRoute(ControllerOne::class, 'index2', '/', []);
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof \LiteApi\Http\Route);
        $this->assertEquals('/', $route->path);
    }

    /**
     * @covers \LiteApi\Http\Router::getRoute
     * @covers \LiteApi\Http\Router::registerRoute
     */
    public function testGetRouteDifferentMethods(): void
    {
        $router = new \LiteApi\Http\Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', ['GET']);
        $router->registerRoute(ControllerOne::class, 'indexPost', '/index', ['POST']);
        $request = new Request(server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof \LiteApi\Http\Route);
        $this->assertEquals(['POST'], $route->httpMethods);
    }
}
