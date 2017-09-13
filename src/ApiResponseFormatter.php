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
     * @param array             $params
     *
     * @return ResponseInterface
     */
    private function setHeaders(ResponseInterface $response, array $params = []): ResponseInterface
    {
        if ($this->request->getAttribute('_api', false)) {
            $requestParams = $this->request->getAttribute('_api');

            if (isset($requestParams['range']) && 2 === count($requestParams['range'])) {
                $headers = $this->getRangeHeader($requestParams, $params);

                foreach ($headers as $key => $header) {
                    $response = $response->withHeader($key, $header);
                }
            }
        }

        return $response;
    }

    /**
     * @param array $requestParams
     * @param array $params
     *
     * @return array
     */
    private function getRangeHeader(array $requestParams, array $params = []): array
    {
        $headers = [];
        $headers['Content-Range'] = $requestParams['range'][0] . '-' . $requestParams['range'][1] .
                                    (null !== $params['count'] ? '/' . $params['count'] : '');

        if (isset($params['count'])) {
            $host = $this->request->getUri()->getScheme() . '://';
            $host .= $this->request->getUri()->getHost();
            $host .= (
            '' !== $this->request->getUri()->getPort() ?
                ':' . $this->request->getUri()->getPort() :
                ''
            );
            $host .= $this->request->getUri()->getPath();
            $host .= (
            '' !== $this->request->getUri()->getQuery() ?
                '?' . $this->request->getUri()->getQuery() :
                ''
            );
            $headers['Link'] = [];
            $headers['Link'][] = '<' . $host . '>; rel="self"';

            echo $host . '<br>';

            foreach (['first', 'prev', 'next', 'last'] as $rel) {
                $exists = true;

                switch ($rel) {
                    case 'first':
                        if ($requestParams['range'][0] === 0) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=0-' . $this->maxRange,
                                $host
                            );
                        }
                        break;
                    case 'prev':
                        if ((($requestParams['range'][0] - $this->maxRange) <= 0)) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($requestParams['range'][0] - $this->maxRange) . '-' . $this->maxRange,
                                $host
                            );
                        }
                        break;
                    case 'next':
                        if (($requestParams['range'][0] + $this->maxRange) <= $requestParams['range'][0] ||

                            ($requestParams['range'][0] + $this->maxRange) >= $params['count']
                        ) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($requestParams['range'][0] + $this->maxRange) . '-' . $this->maxRange,
                                $host
                            );
                        }
                        break;
                    case 'last':
                        if (($params['count'] - $this->maxRange) === $requestParams['range'][0]) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($params['count'] - $this->maxRange) . '-' . $this->maxRange,
                                $host
                            );
                        }
                        break;
                }

                if (true === $exists) {
                    $headers['Link'][] = '<' . $currentHost . '>; rel="' . $rel . '"';
                }
            }
        }

        return $headers;
    }
}
