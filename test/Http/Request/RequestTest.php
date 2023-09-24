<?php

namespace LiteApi\Test\Http\Request;

use LiteApi\Http\Exception\HttpException;
use LiteApi\Http\Request\QueryType;
use LiteApi\Http\Request\Request;
use LiteApi\Http\Response\ResponseStatus;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    private function getRequiredServerParams(): array
    {
        return [
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_URI' => '/'
        ];
    }

    /**
     * @covers \LiteApi\Http\Request\Request::parseQueryByDefinition
     */
    public function testParseQueryByDefinition(): void
    {

        $request = new Request([
            'key1' => 'abc',
            'key2' => 123
        ], server: $this->getRequiredServerParams());
        $definitions = [
            'key1' => QueryType::String,
            'key2' => QueryType::Int,
            'key3' => ResponseStatus::class
        ];
        $request->parseQueryByDefinition($definitions);
        $this->expectException(HttpException::class);
        $definitions = [
            'key1' => QueryType::Int,
            'key2' => QueryType::Int,
            'key3' => ResponseStatus::class
        ];
        $request->parseQueryByDefinition($definitions);
    }

    /**
     * @covers \LiteApi\Http\Request\Request::parseJsonContent
     */
    public function testParseJsonContent(): void
    {
        $content = [
            'key1' => 'abc',
            'key2' => 'sdf'
        ];
        $request = new Request(server: $this->getRequiredServerParams(), content: json_encode($content));
        $request->parseJsonContent(['key1']);
        $request->parseJsonContent(['key1', 'key2']);
        $this->expectException(HttpException::class);
        $request->parseJsonContent(['key3']);
    }
}
