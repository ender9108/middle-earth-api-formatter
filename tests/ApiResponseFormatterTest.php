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
            'sort' => 'firstname,lastname,age,size',
            'desc' => 'age,size',
            'range' => '0-10'
        ]);
        $response = new Response();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $response = $apiFormatter->formatResponse($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
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
