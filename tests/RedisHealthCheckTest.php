<?php
/**
 * Test suite for Redis HealthCheck client
 *
 * @author Ian <ian@ianh.io>
 * @since 29/11/2018
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exception\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\HealthCheck;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\RedisHealthCheck;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisClient;

class RedisHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheckInterface(): void
    {
        $this->assertContains(
            HealthCheck::class,
            class_implements(new RedisHealthCheck('sample-service'))
        );
    }

    /** @test */
    public function whenClientNotSetThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);
        $redisClient = new RedisHealthCheck('sample-service');
        $redisClient->getServiceStatus();
    }

    /** @test */
    public function successWriteReadReturnsSuccessData(): void
    {
        $mock = \Mockery::mock(RedisClient::class);
        $mock->shouldReceive('set')->once()->andReturnTrue();
        $mock->shouldReceive('get')->once()->andReturn('YES');

        $redis = new RedisHealthCheck('sample-service');
        $redis->setClient($mock);

        $expectedResponse = new HealthCheckResponse(
            200,
            'Message successfully stored and retrieved for: sample-service'
        );
        $response = $redis->getServiceStatus();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function failedWriteReadReturnsErrorData(): void
    {
        $mock = \Mockery::mock(RedisClient::class);
        $mock->shouldReceive('set')->once()->andReturnFalse();
        $mock->shouldReceive('get')->once()->andReturn('NO');

        $redis = new RedisHealthCheck('sample-service');
        $redis->setClient($mock);

        $expectedResponse = new HealthCheckResponse(
            500,
            'Failed to store and retrieve message for: sample-service'
        );
        $response = $redis->getServiceStatus();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response);
    }
}
