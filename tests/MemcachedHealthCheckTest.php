<?php
/**
 * Test suite for Memcached healthcheck client
 *
 * @author
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\MemcachedHealthCheckInterface;
use Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheckInterface(): void
    {
        $this->assertContains(
            HealthCheckInterface::class,
            class_implements(new MemcachedHealthCheckInterface('sample-service'))
        );
    }

    /** @test */
    public function whenClientNotSetThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);
        $redisClient = new MemcachedHealthCheckInterface('sample-service');
        $redisClient->getServiceStatus();
    }

    /** @test */
    public function successWriteReadReturnsSuccessData(): void
    {
        $mock = \Mockery::mock(Memcached::class);
        $mock->shouldReceive('set')->once()->andReturn();
        $mock->shouldReceive('get')->once()->andReturn('YES');

        $memcached = new MemcachedHealthCheckInterface('sample-service');
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

        $memcached = new MemcachedHealthCheckInterface('sample-service');
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
