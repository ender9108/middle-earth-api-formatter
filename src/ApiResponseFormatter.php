<?php

namespace EnderLab;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiResponseFormatter
{
    /**
     * @var ServerRequest
     */
    private $request;

    /**
     * @var ApiInterface
     */
    private $api;

    public function __construct(ApiInterface $api, ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->api = $api;
    }

    public function formatResponse(ResponseInterface $response, array $params = []): ResponseInterface
    {
        if ($this->request->getAttribute('_api', false)) {
            $requestParams = $this->request->getAttribute('_api');

            if (isset($requestParams['range']) && 2 === count($requestParams['range'])) {
                if ($requestParams['range'][1] <= $this->api->getMaxRange()) {
                    $headers = $this->getRangeHeader($requestParams, $params);

                    foreach ($headers as $key => $header) {
                        $response = $response->withHeader($key, $header);
                    }

                    if (isset($params['count']) && $params['count'] > $requestParams['range'][1]) {
                        $response = $response->withStatus(206);
                    }
                } else {
                    $response = $response->withStatus(400);
                    $response = $response->withHeader('Content-Type', 'application/json');
                    $response->getBody()->write(json_encode([
                        'error'             => 'range error',
                        'error_description' => 'Requested range not allowed'
                    ]));
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
                                    (isset($params['count']) ? '/' . $params['count'] : '');
        $headers['Accept-Range'] = $this->api->getResourceName() . ' ' . $this->api->getMaxRange();

        if (isset($params['count'])) {
            $host = $this->buildHost();
            $links = ['<' . $host . '>; rel="self"'];
            $currentHost = '';

            foreach (['first', 'prev', 'next', 'last'] as $rel) {
                $exists = true;

                switch ($rel) {
                    case 'first':
                        if ($requestParams['range'][0] === 0) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=0-' . $this->api->getMaxRange(),
                                $host
                            );
                        }
                        break;
                    case 'prev':
                        if ((($requestParams['range'][0] - $this->api->getMaxRange()) <= 0)) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($requestParams['range'][0] - $this->api->getMaxRange()) . '-' . $this->api->getMaxRange(),
                                $host
                            );
                        }
                        break;
                    case 'next':
                        if (($requestParams['range'][0] + $this->api->getMaxRange()) <= $requestParams['range'][0] ||
                            ($requestParams['range'][0] + $this->api->getMaxRange()) >= $params['count']
                        ) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($requestParams['range'][0] + $this->api->getMaxRange()) . '-' . $this->api->getMaxRange(),
                                $host
                            );
                        }
                        break;
                    case 'last':
                        if (($params['count'] - $this->api->getMaxRange()) === $requestParams['range'][0]) {
                            $exists = false;
                        } else {
                            $currentHost = str_replace(
                                'range=' . $requestParams['range'][0] . '-' . $requestParams['range'][1],
                                'range=' . ($params['count'] - $this->api->getMaxRange()) . '-' . $this->api->getMaxRange(),
                                $host
                            );
                        }
                        break;
                }

                if (true === $exists) {
                    $links[] = '<' . $currentHost . '>; rel="' . $rel . '"';
                }
            }

            $headers['Link'] = implode(', ', $links);
        }

        return $headers;
    }

    private function buildHost()
    {
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

        return $host;
    }
}
