<?php
namespace Tests;

use Giffgaff\ServiceHealthCheck\ServiceHealthCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ServiceHealthCheckTest extends TestCase
{
    /**
     * @var ServiceHealthCheck
     */
    protected $healthCheck;
    /**
     * @var array
     */
    protected $services;

    /**
     *
     */
    public function setUp()
    {
        $this->services = [
            'service1' => 'http://service1',
            'service2' => 'http://service2',
            'service3' => 'http://service3',
            'service4' => 'http://service4',
        ];

        $expectedBody = 'test';

        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], $expectedBody),
            new \GuzzleHttp\Psr7\Response(403, [], $expectedBody),
            new \GuzzleHttp\Psr7\Response(404, [], $expectedBody),
            new \GuzzleHttp\Exception\RequestException(
                'Cannot connect to server',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            ),
        ]);

        $client = new \GuzzleHttp\Client(['handler' => $mock]);
        $this->healthCheck = new ServiceHealthCheck($client, $this->services);

        parent::setUp();
    }

    /** @test */
    public function returnsValidStatusCode(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $this->assertEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function returnsValidResponse(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $expectedBody = '{"service1":{"status":200,"data":"test"},'
                        . '"service2":{"status":403,"data":"test"},'
                        . '"service3":{"status":404,"data":"test"},'
                        . '"service4":{"status":500,"data":"Request failed for service: service4"}}';

        $this->assertEquals($expectedBody, $response->getBody()->getContents());
    }

    /** @test */
    public function loadingConfigReturnsAPopulatedArray(): void
    {
        $config = Yaml::parseFile(__DIR__ . '/_config/test_config.yml');

        $this->assertCount(3, $config['services']);
    }
}
