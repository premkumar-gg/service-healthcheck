<?php

use Icawebdesign\ServiceHealthCheck\HealthCheck;
use PHPUnit\Framework\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * @var HealthCheck
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
        ];

        $this->healthCheck = new HealthCheck($this->client, $this->services);
    }

    /** @test */
    public function successfulCheckReturns200StatusCode(): void
    {
        $response = $this->healthCheck->getServiceStatuses();

        $this->assertEquals(200, $response->getStatusCode());
    }
}