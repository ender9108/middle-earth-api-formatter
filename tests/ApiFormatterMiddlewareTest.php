<?php

namespace Tests\EnderLab;

use EnderLab\Middlewares\ApiFormatterMiddleware;
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
            '/api/v1/users'
        );
        $request = $request->withQueryParams(['fields' => 'firstname,lastname,address(country,city)']);
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testParseSortParam()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users'
        );
        $request = $request->withQueryParams(['sort' => 'firstname,lastname,age,size', 'desc' => 'age,size']);
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testParseRangeParam()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users'
        );
        $request = $request->withQueryParams(['range' => '0-10']);
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testParseRangeParamWithError()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users'
        );
        $request = $request->withQueryParams(['range' => '10']);
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $this->expectException(\InvalidArgumentException::class);
        $apiFormatter->process($request, $delegate);
    }

    public function testParseQueryFiltersParam()
    {
        $request = new ServerRequest(
            'GET',
            '/api/v1/users'
        );
        $request = $request->withQueryParams(['bidule' => 'truc', 'machin' => 'chose']);
        $delegate = new Dispatcher();
        $apiFormatter = new ApiFormatterMiddleware();
        $response = $apiFormatter->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
