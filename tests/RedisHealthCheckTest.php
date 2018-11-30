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
use Predis\Client;

class RedisHealthCheckTest extends TestCase
{
    /** @test */
    public function successWriteReadReturnsSuccessData(): void
    {
        $mock = \Mockery::mock(Client::class);
        $mock->shouldReceive('disconnect')->once()->andReturn();
        $mock->shouldReceive('set')->once()->andReturnTrue();
        $mock->shouldReceive('get')->once()->andReturn('YES');

        $redis = new RedisHealthCheck('sampleService');
        $response = $redis->getServiceStatus();
        $body = $response->getBody()->getContents();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($body);
        $this->assertJsonStringEqualsJsonString(
            '[{"status":200,"data":"Message successfully stored and retrieved"}]',
            $body
        );
    }

    /** @test */
    public function failedWriteReadReturnsErrorData(): void
    {
        $mock = \Mockery::mock(Client::class);
        $mock->shouldReceive('disconnect')->andReturn();
        $mock->shouldReceive('set')->once()->andReturnFalse();
        $mock->shouldReceive('get')->once()->andReturn('NO');

        $redis = new RedisHealthCheck([$mock]);
        $response = $redis->getServiceStatuses();
        $body = $response->getBody()->getContents();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJson($body);
        $this->assertJsonStringEqualsJsonString(
            '[{"status":500,"data":"Failed to store and retrieve message"}]',
            $body
        );
    }
}
