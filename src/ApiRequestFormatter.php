<?php

namespace EnderLab;

use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiRequestFormatter implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $params = [];

        if (isset($queryParams['fields'])) {
            $params['fields'] = $this->parseStringInfos($queryParams['fields']);
            unset($queryParams['fields']);
        }

        if (isset($queryParams['sort'])) {
            $params['sort'] = $this->getSortInfos(
                $queryParams['sort'],
                (isset($queryParams['desc']) ? $queryParams['desc'] : null)
            );
            unset($queryParams['sort']);

            if (isset($queryParams['desc'])) {
                unset($queryParams['desc']);
            }
        }

        if (isset($queryParams['range'])) {
            $params['range'] = $this->getRangeInfos($queryParams['range']);
            unset($queryParams['range']);
        }

        if (count($queryParams) > 0) {
            $params['filters'] = [];

            foreach ($queryParams as $key => $value) {
                $params['filters'][$key] = $this->parseStringInfos($value);
            }
        }

        $request = $request->withAttribute('_api', $params);

        return $requestHandler->handle($request);
    }

    /**
     * Parse query fields infos.
     *
     * fields=attribute1,attributeN
     *   Ex: GET /clients/007?fields=firstname,name
     * fields=object(attribute1,attributeN)
     *   Ex: GET /clients/007?fields=firstname,name,address(street)
     *
     * @param string $string
     *
     * @return array
     */
    private function parseStringInfos(string $string): array
    {
        $result = [];
        $length = mb_strlen($string);
        $count = 0;
        $subCount = 0;
        $startBracket = false;
        $masterField = null;

        for ($i = 0; $i < $length; ++$i) {
            if (',' === $string[$i]) {
                if (false === $startBracket) {
                    ++$count;
                } else {
                    ++$subCount;
                }
            } elseif ('(' === $string[$i]) {
                $startBracket = true;
                $masterField = $result[$count];
                $result[$count] = [$masterField => []];
            } elseif (')' === $string[$i]) {
                $startBracket = false;
                $subCount = 0;
                $masterField = null;
            } else {
                if (!isset($result[$count])) {
                    $result[$count] = '';
                }

                if (true === $startBracket &&
                    null !== $masterField &&
                    !isset($result[$count][$masterField][$subCount])
                ) {
                    $result[$count][$masterField][$subCount] = '';
                }

                if (true === $startBracket) {
                    $result[$count][$masterField][$subCount] .= $string[$i];
                } else {
                    $result[$count] .= $string[$i];
                }
            }
        }

        return $result;
    }

    /**
     * Parse query sort infos
     * sort=attribute1,attributeN&desc=attribute1
     *   Ex: GET /clients/007?sort=firstname,name&desc=name.
     *
     * @param string      $sort
     * @param string|null $desc
     *
     * @return array
     */
    private function getSortInfos(string $sort, ?string $desc = null): array
    {
        $result = ['asc' => [], 'desc' => []];
        $descList = [];

        if (null !== $desc) {
            $descList = ('' === trim($desc) ? null : $this->parseStringInfos($desc));
        }

        $sortList = $this->parseStringInfos($sort);

        foreach ($sortList as $key => $value) {
            if (null !== $descList && in_array($value, $descList, true)) {
                $result['desc'][] = $value;
            } else {
                $result['asc'][] = $value;
            }
        }

        return $result;
    }

    /**
     * Parse query range infos
     * range=0-25
     *   Ex: GET /clients/007?range=0-25.
     *
     * @param string $range
     *
     * @return array
     */
    private function getRangeInfos(string $range): array
    {
        $result = explode('-', $range);

        if (2 !== count($result)) {
            throw new \InvalidArgumentException(
                'Parameters "range" malformed. Must expected two parameters range=[offset]-[limit]'
            );
        }

        return $result;
    }
}
