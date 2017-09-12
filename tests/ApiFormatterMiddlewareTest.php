<?php

namespace Tests\EnderLab;

use EnderLab\ApiFormatterMiddleware;
use EnderLab\Dispatcher\Dispatcher;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiFormatterMiddlewareTest extends TestCase
{
    public function testCreateMiddleware()
    {
        $apiFormatter = new ApiFormatterMiddleware();
        $this->assertInstanceOf(ApiFormatterMiddleware::class, $apiFormatter);
    }

    public function testParseFieldsParam()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users?fields=firstname,lastname,address(country,city)'
        );
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testParseSortParam()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users?sort=firstname,lastname&desc=age'
        );
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
