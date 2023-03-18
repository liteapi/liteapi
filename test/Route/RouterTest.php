<?php

namespace LiteApi\Test\Route;

use Exception;
use LiteApi\Route\Route;
use LiteApi\Route\Router;
use PHPUnit\Framework\TestCase;
use LiteApi\Http\Exception\MethodNotAllowedHttpException;
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
        $this->assertEquals(ResponseStatus::INTERNAL_SERVER_ERROR, $response->status);
        $exception = new MethodNotAllowedHttpException('Something wrong happened');
        $response = $router->getErrorResponse($exception);
        $this->assertEquals('Something wrong happened', $response->content);
        $this->assertEquals(ResponseStatus::METHOD_NOT_ALLOWED, $response->status);
    }

    /**
     * @covers \LiteApi\Route\Router::getRoute
     * @covers \LiteApi\Route\Router::registerRoute
     */
    public function testGetRoute(): void
    {
        $loader = new Router();
        $loader->registerRoute(ControllerOne::class, 'index', '/index', []);
        $request = new Request([], [], [], [], ['REQUESTED_METHOD' => 'GET', 'REQUEST_URI' => '/index', 'REMOTE_ADDR' => '127.0.0.1']);
        $route = $loader->getRoute($request);
        $this->assertTrue($route instanceof Route);
    }
}
