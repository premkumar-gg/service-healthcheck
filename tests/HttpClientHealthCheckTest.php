<?php
/**
 * Tests for HttpClientHealthCheckInterface
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheckInterface;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
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
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
        $this->assertContains(HealthCheckInterface::class, class_implements($httpClientHealthCheck));
    }

    /** @test */
    public function whenClientNotSetThrowsException(): void
    {
        $this->expectException(InvalidOperationException::class);
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
        $httpClientHealthCheck->getServiceStatus();
    }

    /** @test */
    public function whenRequestNotSetThrowsException()
    {
        $this->expectException(InvalidOperationException::class);
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
        $httpClientHealthCheck->setClient(new Client());
        $httpClientHealthCheck->getServiceStatus();
    }

    /** @test */
    public function whenRequestMadeWithValidUriOnlyReturnsAHealthCheckResponseWithHttpRequest(): void
    {
        // setup
        $mock = new MockHandler([
            new Response(200, [], 'test response'),
        ]);
        $client = new Client(['handler' => $mock]);

        $request = new Request('GET', 'http://service');

        $expectedHealthCheckResponse = new HealthCheckResponse(
            200,
            'test response',
            false,
            $request
        );

        // when
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);

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
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
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
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
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
        $request = new Request('GET', 'http://service', [], $requestBody);

        $mock = new MockHandler([
            new RequestException(
                'Cannot connect to server',
                $request
            ),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheckInterface('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);
        $response = $httpClientHealthCheck->getServiceStatus();

        // then
        $expectedResponse = new HealthCheckResponse(
            500,
            'Request failed for service: sampleService',
            false,
            $request
        );
        $this->assertEquals($expectedResponse, $response);
    }
}
