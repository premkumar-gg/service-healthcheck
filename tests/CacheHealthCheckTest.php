<?php
/**
 * Tests for CacheHealthCheckInterface
 */

namespace Tests;


use Giffgaff\ServiceHealthCheck\CacheHealthCheckInterface;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\Interfaces\CacheInterface;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use PHPUnit\Framework\TestCase;

class CacheHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheck(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');
        $this->assertContains(HealthCheckInterface::class, class_implements($cacheHealthCheck));
    }

    /** @test */
    public function responds200WhenCanReadAndWriteIntoCache(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');

        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('save')->once()->andReturn('YES');
        $mockedCache->shouldReceive('load')->once()->andReturn('YES');
        $mockedCache->shouldReceive('remove')->once()->andReturn('YES');

        $cacheHealthCheck->setCache($mockedCache);

        $response = $cacheHealthCheck->getServiceStatus();
        $expectedResponse = new HealthCheckResponse(
            200,
            'Able to write and read some data in sampleService'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function responds500WhenReadValueIsNotTheSameAsWritten(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');

        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('save')->once()->andReturn('YES');
        $mockedCache->shouldReceive('load')->once()->andReturn('SOMETHINGELSE');
        $mockedCache->shouldReceive('remove')->once()->andReturn('YES');

        $cacheHealthCheck->setCache($mockedCache);

        $response = $cacheHealthCheck->getServiceStatus();
        $expectedResponse = new HealthCheckResponse(
            500,
            'Something went wrong while writing into sampleService. The value read is not the same as the written.'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function responds500WhenSaveFails(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');

        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('save')->once()->andThrows(new \Exception("Cannot save"));
        $mockedCache->shouldReceive('load')->once()->andReturn('SOMETHINGELSE');
        $mockedCache->shouldReceive('remove')->once()->andReturn('YES');

        $cacheHealthCheck->setCache($mockedCache);

        $response = $cacheHealthCheck->getServiceStatus();
        $expectedResponse = new HealthCheckResponse(
            500,
            'Fatal error while testing sampleService.'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function responds500WhenLoadFails(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');

        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('save')->once()->andReturn('YES');
        $mockedCache->shouldReceive('load')->once()->andThrows(new \Exception("Cannot save"));
        $mockedCache->shouldReceive('remove')->once()->andReturn('YES');

        $cacheHealthCheck->setCache($mockedCache);

        $response = $cacheHealthCheck->getServiceStatus();
        $expectedResponse = new HealthCheckResponse(
            500,
            'Fatal error while testing sampleService.'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function responds500WhenRemoveFails(): void
    {
        $cacheHealthCheck = new CacheHealthCheckInterface('sampleService');

        $mockedCache = \Mockery::mock(CacheInterface::class);
        $mockedCache->shouldReceive('save')->once()->andReturn('YES');
        $mockedCache->shouldReceive('load')->once()->andReturn('YES');
        $mockedCache->shouldReceive('remove')->once()->andThrows(new \Exception("Cannot save"));

        $cacheHealthCheck->setCache($mockedCache);

        $response = $cacheHealthCheck->getServiceStatus();
        $expectedResponse = new HealthCheckResponse(
            500,
            'Fatal error while testing sampleService.'
        );

        $this->assertEquals($expectedResponse, $response);
    }
}
