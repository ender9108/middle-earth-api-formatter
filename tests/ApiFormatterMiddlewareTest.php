<?php

namespace Tests\EnderLab;

use EnderLab\ApiFormatterMiddleware;
use PHPUnit\Framework\TestCase;

class ApiFormatterMiddlewareTest extends TestCase
{
    public function testCreateMiddleware()
    {
        $apiFormatter = new ApiFormatterMiddleware();
        $this->assertInstanceOf(ApiFormatterMiddleware::class, $apiFormatter);
    }
}
