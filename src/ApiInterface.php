<?php

namespace EnderLab;

use Psr\Http\Message\ServerRequestInterface;

interface ApiInterface
{
    /**
     * @return string
     */
    public function getResourceName(): string;

    /**
     * @return int
     */
    public function getMaxRange(): int;

    /**
     * @return string
     */
    public function getHeaderLink(ServerRequestInterface $request): string;
}
