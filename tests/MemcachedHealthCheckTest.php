<?php
/**
 * Test suite for Memcached healthcheck client
 *
 * @author
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exception\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\MemcachedHealthCheck;
use Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheckInterface(): void
    {
        $this->assertContains(
            HealthCheck::class,
            class_implements(new MemcachedHealthCheck('sample-service'))
        );
    }

    /** @test */
    public function whenClientNotSetThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);
        $redisClient = new MemcachedHealthCheck('sample-service');
        $redisClient->getServiceStatus();
    }

    /** @test */
    public function successWriteReadReturnsSuccessData(): void
    {
        $mock = \Mockery::mock(Memcached::class);
        $mock->shouldReceive('set')->once()->andReturn();
        $mock->shouldReceive('get')->once()->andReturn('YES');

        $memcached = new MemcachedHealthCheck('sample-service');
        $memcached->setClient($mock);

        $expectedResponse = new HealthCheckResponse(
            200,
            'Message successfully stored and retrieved for: sample-service'
        );
        $response = $memcached->getServiceStatus();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function failedWriteReadReturnsErrorData(): void
    {
        $mock = \Mockery::mock(Memcached::class);
        $mock->shouldReceive('set')->once()->andReturnFalse();
        $mock->shouldReceive('get')->once()->andReturn('NO');

        $memcached = new MemcachedHealthCheck('sample-service');
        $memcached->setClient($mock);

        $expectedResponse = new HealthCheckResponse(
            500,
            'Failed to store and retrieve message for: sample-service'
        );
        $response = $memcached->getServiceStatus();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response);
    }
}
