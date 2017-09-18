<?php

namespace EnderLab;

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
     * @return array
     */
    public function getHeaderLink(): array;
}
