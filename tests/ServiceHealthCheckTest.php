<?php
namespace Tests;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheck;
use Giffgaff\ServiceHealthCheck\ServiceHealthCheck;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class ServiceHealthCheckTest extends TestCase
{
    /** @var ServiceHealthCheck */
    protected $healthCheck;
    /** @var ServiceHealthCheck */
    protected $healthCheckDebugMode;
    /** @var array */
    protected $servicesDebugMode;
    /** @var array */
    protected $services;

    /**
     * @return HttpClientHealthCheck|\Mockery\MockInterface
     */
    protected function getMockedHttpClientHealthCheck()
    {
        $mock = \Mockery::mock(HttpClientHealthCheck::class);
        $mock->shouldReceive('setClient')->once()->andReturn();
        $mock->shouldReceive('setRequest')->once()->andReturn();
        $mock->setClient(new Client());

        return $mock;
    }

    /**
     *
     */
    public function setUp()
    {
        $service1DebugMock = $this->getMockedHttpClientHealthCheck();
        $service1DebugMock->shouldReceive('getServiceStatus')->once()->andReturn(
            new HealthCheckResponse(200, 'positive response', true)
        );
        $service1DebugMock->setRequest(new Request('GET', 'http://service1'));

        $service2DebugMock = $this->getMockedHttpClientHealthCheck();
        $service2DebugMock->shouldReceive('getServiceStatus')->once()->andReturn(
            new HealthCheckResponse(403, 'negative response', true)
        );
        $service2DebugMock->setRequest(new Request('GET', 'http://service2'));

        $this->servicesDebugMode = [
            'service1' => $service1DebugMock,
            'service2' => $service2DebugMock,
        ];

        $this->healthCheckDebugMode = new ServiceHealthCheck($this->servicesDebugMode);

        $service1Mock = $this->getMockedHttpClientHealthCheck();
        $service1Mock->shouldReceive('getServiceStatus')->once()->andReturn(
            new HealthCheckResponse(200, 'positive response', false)
        );
        $service1Mock->setRequest(new Request('GET', 'http://service1'));

        $service2Mock = $this->getMockedHttpClientHealthCheck();
        $service2Mock->shouldReceive('getServiceStatus')->once()->andReturn(
            new HealthCheckResponse(403, 'negative response', false)
        );
        $service2Mock->setRequest(new Request('GET', 'http://service2'));

        $this->services = [
            'service1' => $service1Mock,
            'service2' => $service2Mock,
        ];

        $this->healthCheck = new ServiceHealthCheck($this->services);

        parent::setUp();
    }

    /** @test */
    public function returnsValidStatusCode(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function returnsValidDebugResponse(): void
    {
        $response = $this->healthCheckDebugMode->getServiceStatuses();

        $expectedBody = '{"service1":{"status":200,"data":"positive response"},'
            . '"service2":{"status":403,"data":"negative response"}}';

        $this->assertEquals($expectedBody, $response->getBody()->getContents());
    }

    /** @test */
    public function returnsValidResponse(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $expectedBody = '{"service1":{"status":200,"data":"UP"},'
            . '"service2":{"status":403,"data":"DOWN"}}';

        $this->assertEquals($expectedBody, $response->getBody()->getContents());
    }

    /** @test */
    public function whenOneHealthCheckInstanceIsNotValidThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);

        $services = [
            'service1' => 'invalid-service-instance--should-be-instanceof-HealthCheck-interface'
        ];

        (new ServiceHealthCheck($services))
            ->getServiceStatuses();
    }
}
