<?php
namespace EnderLab;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiResponseFormatter
{
    /**
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var int
     */
    protected $maxRange;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function formatResponse(ResponseInterface $response, array $params = []): ResponseInterface
    {
        $response = $this->setHeaders($response, $params);

        return $response;
    }

    /**
     * @return string
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     */
    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @return int
     */
    public function getMaxRange(): int
    {
        return $this->maxRange;
    }

    /**
     * @param int $maxRange
     */
    public function setMaxRange(int $maxRange): void
    {
        $this->maxRange = $maxRange;
    }

    /**
     * @param ResponseInterface $response
     * @param array $params
     * @return ResponseInterface
     */
    private function setHeaders(ResponseInterface $response, array $params = []): ResponseInterface
    {
        if ($this->request->getAttribute('_api', false)) {
            $requestParams = $this->request->getAttribute('_api');

            if (isset($requestParams['range']) && count($requestParams['range']) == 2) {
                $headers = $this->getRangeHeader($params);

                foreach ($headers as $key => $header) {
                    $response = $response->withHeader($key, $headers);
                }
            }
        }

        return $response;
    }

    private function getRangeHeader(array $params = [])
    {
        $headers = [];
        $headers['Content-Range'] = $requestParams['range'][0].'-'.$requestParams['range'][1].
                                    ( null !== $params['count'] ?: '/'.$params['count'] );

        if (isset($params['count'])) {
            $host = $this->request->getUri()->getScheme() . '://';
            $host .= $this->request->getUri()->getHost();
            $host .= (
            trim($this->request->getUri()->getPort()) != '' ?
                ':' . $this->request->getUri()->getPort() :
                ''
            );
            $host .= $this->request->getUri()->getPath();
            $host .= (
            trim($this->request->getUri()->getQuery()) != '' ?
                '?' . $this->request->getUri()->getQuery() :
                ''
            );
            $headers['Link'] = [];

            foreach (['first', 'prev', 'next', 'last'] as $rel) {
                switch ($rel) {
                    case 'first':
                        $currentHost = str_replace(
                            'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                            'range=0-' . $this->maxRange,
                            $host
                        );
                        break;
                    case 'prev':

                        break;
                    case 'next':

                        break;
                    case 'last':
                        $currentHost = str_replace(
                            'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                            'range='.($params['count']-$this->maxRange).'-' . $this->maxRange,
                            $host
                        );
                        break;
                }

                $headers['Link'][] = '<' . $currentHost . '>; rel="' . $rel . '"';
            }

            /*
            https://api.fakecompany.com/v1/orders?range=48-55
            < Link: <https://api.fakecompany.com/v1/orders?range=0-7>; rel="first"
            < Link: <https://api.fakecompany.com/v1/orders?range=40-47>; rel="prev"
            < Link: <https://api.fakecompany.com/v1/orders?range=56-64>; rel="next"
            < Link: <https://api.fakecompany.com/v1/orders?range=968-975>; rel="last"
             */
        }
    }
}