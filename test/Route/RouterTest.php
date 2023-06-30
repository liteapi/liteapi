<?php

namespace LiteApi\Test\Route;

use Exception;
use LiteApi\Http\Exception\HttpException;
use LiteApi\Route\Route;
use LiteApi\Route\Router;
use PHPUnit\Framework\TestCase;
use LiteApi\Http\Request;
use LiteApi\Http\ResponseStatus;
use LiteApi\Test\resources\classes\ControllerOne;

/**
 * @covers \LiteApi\Route\Router
 */
class RouterTest extends TestCase
{

    /**
     * @covers \LiteApi\Route\Router::getErrorResponse
     */
    public function testGetErrorResponse(): void
    {
        $router = new Router();
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
     * @covers \LiteApi\Route\Router::getRoute
     * @covers \LiteApi\Route\Router::registerRoute
     */
    public function testGetRoute(): void
    {
        $router = new Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', []);
        $request = new Request([], [], [], [], ['REQUESTED_METHOD' => 'GET', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof Route);
    }

    /**
     * @covers \LiteApi\Route\Router::getRoute
     * @covers \LiteApi\Route\Router::registerRoute
     */
    public function testGetEmptyRoute(): void
    {
        $router = new Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', []);
        $router->registerRoute(ControllerOne::class, 'index2', '/', []);
        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals('/', $route->path);
    }

    /**
     * @covers \LiteApi\Route\Router::getRoute
     * @covers \LiteApi\Route\Router::registerRoute
     */
    public function testGetRouteDifferentMethods(): void
    {
        $router = new Router();
        $router->registerRoute(ControllerOne::class, 'index', '/index', ['GET']);
        $router->registerRoute(ControllerOne::class, 'indexPost', '/index', ['POST']);
        $request = new Request(server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $router->getRoute($request);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals(['POST'], $route->httpMethods);
    }
}
