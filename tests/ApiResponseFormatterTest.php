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
    private function makeRequest($attributes = [], $query = '')
    {
        $request = new ServerRequest('GET', '/tests');

        foreach ($attributes as $key => $attribute) {
            $request = $request->withAttribute($key, $attribute);
        }

        $uri = $request->getUri();
        $uri = $uri->withQuery($query);
        $uri = $uri->withScheme('http');
        $uri = $uri->withPort('8080');
        $uri = $uri->withHost('localhost');

        $request = $request->withUri($uri);

        return $request;
    }

    public function testCreateInstance()
    {
        $request = $this->makeRequest();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $this->assertInstanceOf(ApiResponseFormatter::class, $apiFormatter);
    }

    public function testFormatInstance1()
    {
        $range = [20, 10];
        $request = $this->makeRequest(
            [
                '_api' => [
                    'fields' => ['firstname', 'lastname'],
                    'sort'   => ['asc' => ['firstname', 'lastname'], 'desc' => ['age']],
                    'range'  => [$range[0], $range[1]]
                ]
            ],
            '?fields=firstname,lastname&sort=firstname,lastname,age&desc=age&range=' . $range[0] . '-' . $range[1] . '&test=bidule'
        );

        $response = new Response();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $response = $apiFormatter->formatResponse($response, ['count' => 100]);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(1, count($response->getHeader('Link')));
        $this->assertSame('tests 10', $response->getHeader('Accept-Range')[0]);
        $this->assertSame('20-10/100', $response->getHeader('Content-Range')[0]);
    }

    public function testFormatInstance2()
    {
        $range = [0, 10];
        $request = $this->makeRequest(
            [
                '_api' => [
                    'fields' => ['firstname', 'lastname'],
                    'sort'   => ['asc' => ['firstname', 'lastname'], 'desc' => ['age']],
                    'range'  => [$range[0], $range[1]]
                ]
            ],
            '?fields=firstname,lastname&sort=firstname,lastname,age&desc=age&range=' . $range[0] . '-' . $range[1] . '&test=bidule'
        );

        $response = new Response();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $response = $apiFormatter->formatResponse($response, ['count' => 100]);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(1, count($response->getHeader('Link')));
        $this->assertSame('tests 10', $response->getHeader('Accept-Range')[0]);
        $this->assertSame('0-10/100', $response->getHeader('Content-Range')[0]);
    }

    public function testFormatInstance3()
    {
        $range = [0, 10];
        $request = $this->makeRequest(
            [
                '_api' => [
                    'fields' => ['firstname', 'lastname'],
                    'sort'   => ['asc' => ['firstname', 'lastname'], 'desc' => ['age']],
                    'range'  => [$range[0], $range[1]]
                ]
            ],
            '?fields=firstname,lastname&sort=firstname,lastname,age&desc=age&range=' . $range[0] . '-' . $range[1] . '&test=bidule'
        );

        $response = new Response();
        $apiFormatter = new ApiResponseFormatter(new ApiMiddlewareTest(), $request);
        $response = $apiFormatter->formatResponse($response, ['count' => 10]);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(1, count($response->getHeader('Link')));
        $this->assertSame('tests 10', $response->getHeader('Accept-Range')[0]);
        $this->assertSame('0-10/10', $response->getHeader('Content-Range')[0]);
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
        return 10;
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
