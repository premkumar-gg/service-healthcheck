<?php
/**
 * Tests for HttpClientHealthCheck
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exception\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\HealthCheck;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheck;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class HttpClientHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheck(): void
    {
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $this->assertContains(HealthCheck::class, class_implements($httpClientHealthCheck));
    }

    /** @test */
    public function whenClientNotSetThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->getServiceStatus();
    }

    /** @test */
    public function whenRequestNotSetThrowsException()
    {
        $this->expectException(InvalidOperationException::class);
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient(new Client());
        $httpClientHealthCheck->getServiceStatus();
    }

    /** @test */
    public function whenRequestMadeWithValidUriOnlyReturnsAHealthCheckResponse(): void
    {
        // setup
        $mock = new MockHandler([
            new Response(200, [], 'test response'),
        ]);
        $client = new Client(['handler' => $mock]);
        $expectedHealthCheckResponse = new HealthCheckResponse(200, 'test response');

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest(new Request('GET', 'http://service'));

        // then
        $this->assertEquals($expectedHealthCheckResponse, $httpClientHealthCheck->getServiceStatus());
    }

    /** @test */
    public function whenRequestSetWithHeadersClientMakesRequestWithHeaders(): void
    {
        // setup
        $requestHeaders = [
            'X-Auth' => 'some-auth-code',
            'Content-type' => 'application/json'
        ];
        $mock = new MockHandler([
            new Response(200, [], 'test response'),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest(new Request('GET', 'http://service', $requestHeaders));
        $httpClientHealthCheck->getServiceStatus();

        // then
        $lastRequestHeaders = $mock->getLastRequest()->getHeaders();
        $this->assertEquals('some-auth-code', $lastRequestHeaders['X-Auth'][0]);
        $this->assertEquals('application/json', $lastRequestHeaders['Content-type'][0]);
    }

    /** @test */
    public function whenRequestSetWithBodyClientMakesRequestWithBody(): void
    {
        // setup
        $requestBody = 'test-request-body';
        $mock = new MockHandler([
            new Response(200, [], 'test response'),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest(new Request('GET', 'http://service', [], $requestBody));
        $httpClientHealthCheck->getServiceStatus();

        // then
        $lastRequestBody = $mock->getLastRequest()->getBody()->getContents();
        $this->assertEquals('test-request-body', $lastRequestBody);
    }

    /** @test */
    public function whenRequestFailsFatally500StatusIsReceived(): void
    {
        // setup
        $requestBody = 'test-request-body';
        $mock = new MockHandler([
            new RequestException(
                'Cannot connect to server',
                new Request('GET', 'http://service')
            ),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest(new Request('GET', 'http://service', [], $requestBody));
        $response = $httpClientHealthCheck->getServiceStatus();

        // then
        $expectedResponse = new HealthCheckResponse(500, 'Request failed for service: sampleService');
        $this->assertEquals($expectedResponse, $response);
    }
}
