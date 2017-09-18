<?php

namespace Tests\EnderLab;

use EnderLab\ApiInterface;
use EnderLab\ApiResponseFormatter;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiResponseFormatterTest extends TestCase
{
    public function testCreateInstance()
    {
        $request = new ServerRequest('GET', '/tests');
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $this->assertInstanceOf(ApiResponseFormatter::class, $apiFormatter);
    }

    public function testFormatInstance()
    {
        $request = new ServerRequest('GET', '/tests');
        $request = $request->withAttribute('_api', [
            'fields' => ['firstname', 'lastname'],
            'sort' => ['asc' => ['firstname', 'lastname'], 'desc' => ['age']],
            'range' => [0, 10]
        ]);
        $response = new Response();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $response = $apiFormatter->formatResponse($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
    /*
     * [fields] => Array(
        [0] => firstname
        [1] => lastname
        [4] => Array(
            [address] => Array(
                [0] => city
                [1] => street
            )
        )
    )
    [sort] => Array(
        [asc] => Array(
            [0] => firstname
            [1] => lastname
        )
        [desc] => Array(
            [0] => age
        )
    )
    [range] => Array(
        [0] => 0
        [1] => 10
    )
    [filters] => Array(
        [test] => bidule
    )
     */
}

class ApiMiddlewareTest implements MiddlewareInterface, ApiInterface
{
    /**
     * @return string
     */
    public function getResourceName(): string
    {
        return 'tests';
    }

    /**
     * @return int
     */
    public function getMaxRange(): int
    {
        return 50;
    }

    /**
     * @return array
     */
    public function getHeaderLink(): array
    {
        return [];
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);
        $apiFormatter = new ApiResponseFormatter($this, $request);
        $response = $apiFormatter->formatResponse($response);
    }
}
