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
     * @return string
     */
    public function getHeaderLink(): string;
}
