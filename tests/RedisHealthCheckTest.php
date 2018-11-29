<?php
/**
 * DESCRIPTION_HERE
 *
 * @author Ian <ian@ianh.io>
 * @since 29/11/2018
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\RedisHealthCheck;
use PHPUnit\Framework\TestCase;

class RedisHealthCheckTest extends TestCase
{
    /** @test */
    public function successWriteReadReturnsSuccessString()
    {
        $redis = new RedisHealthCheck('127.0.0.1', 16379);

        $this->assertEquals('YES', $redis->get('test-message'));
    }
}
