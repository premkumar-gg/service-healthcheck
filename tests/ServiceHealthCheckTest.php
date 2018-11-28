<?php

use Icawebdesign\ServiceHealthCheck\Exception\ConfigNotFoundException;
use Icawebdesign\ServiceHealthCheck\Exception\InvalidConfigException;
use Icawebdesign\ServiceHealthCheck\ServiceHealthCheck;
use PHPUnit\Framework\TestCase;

class ServiceHealthCheckTest extends TestCase
{
    /**
     * @var ServiceHealthCheck
     */
    protected $healthCheck;

    protected $client;

    protected $services;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \GuzzleHttp\Client();
        $this->services = [
            'todos' => 'https://jsonplaceholder.typicode.com/todos/1',
            'albums' => 'https://jsonplaceholder.typicode.com/albums',
        ];

        $this->healthCheck = new ServiceHealthCheck($this->client, $this->services);
    }

    /** @test */
    public function successfulCheckReturns200StatusCode(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function loadingConfigReturnsAPopulatedArray(): void
    {
        $config = $this->healthCheck->loadConfig(__DIR__ . '/_config/test_config.yml');

        $this->assertInternalType('array', $config);
        $this->assertCount(3, $config);
    }

    /** @test */
    public function missingConfigFileThrowsException(): void
    {
        $this->expectException(ConfigNotFoundException::class);

        $this->healthCheck->loadConfig('missing_file.yml');
    }

    /** @test */
    public function invalidConfigFileThrowsException()
    {
        $this->expectException(InvalidConfigException::class);

        $this->healthCheck->loadConfig(__DIR__ . '/_config/invalid_config.yml');
    }
}