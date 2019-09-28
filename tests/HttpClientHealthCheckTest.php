<?php
/**
 * Tests for HttpClientHealthCheck
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheck;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HttpClientHealthCheckTest extends TestCase
{
    /** @test */
    public function implementsHealthCheck(): void
    {
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $this->assertContains(HealthCheckInterface::class, class_implements($httpClientHealthCheck));
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
            $request
        );

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->twice();
        $httpClientHealthCheck->setLogger($mockedLogger);

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

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->twice();
        $httpClientHealthCheck->setLogger($mockedLogger);

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

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->twice();
        $httpClientHealthCheck->setLogger($mockedLogger);

        $httpClientHealthCheck->getServiceStatus();

        // then
        $lastRequestBody = $mock->getLastRequest()->getBody()->getContents();
        $this->assertEquals('test-request-body', $lastRequestBody);
    }

    /** @test */
    public function whenAServiceFailsResponseCodeOfServiceIsCascaded(): void
    {
        // setup
        $requestBody = 'test-request-body';
        $request = new Request('GET', 'http://service', [], $requestBody);

        $mock = new MockHandler([
            new RequestException(
                'Cannot connect to server',
                $request,
                new Response(404)
            ),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->once();
        $mockedLogger->shouldReceive('error')->once();
        $httpClientHealthCheck->setLogger($mockedLogger);

        $response = $httpClientHealthCheck->getServiceStatus();

        // then
        $expectedResponse = new HealthCheckResponse(
            404,
            'Request failed for service: sampleService',
            $request
        );
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function whenRequestFailsFatallyRespondsWith500(): void
    {
        // setup
        $requestBody = 'test-request-body';
        $request = new Request('GET', 'http://service', [], $requestBody);

        $mock = new MockHandler([
            new RequestException(
                'Cannot connect to server',
                $request,
                null
            ),
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->once();
        $mockedLogger->shouldReceive('error')->once();
        $httpClientHealthCheck->setLogger($mockedLogger);

        $response = $httpClientHealthCheck->getServiceStatus();

        // then
        $expectedResponse = new HealthCheckResponse(
            500,
            'Request failed for service: sampleService',
            $request
        );
        $this->assertEquals($expectedResponse, $response);
    }

    /** @test */
    public function whenRequestFailsFatallyWithGenericExceptionRespondsWith500(): void
    {
        // setup
        $requestBody = 'test-request-body';
        $request = new Request('GET', 'http://service', [], $requestBody);

        $mock = new MockHandler([
            new \Exception('Some fatal issue')
        ]);
        $client = new Client(['handler' => $mock]);

        // when
        $httpClientHealthCheck = new HttpClientHealthCheck('sampleService');
        $httpClientHealthCheck->setClient($client);
        $httpClientHealthCheck->setRequest($request);

        $mockedLogger = \Mockery::mock(LoggerInterface::class);
        $mockedLogger->shouldReceive('info')->once();
        $mockedLogger->shouldReceive('error')->once();
        $httpClientHealthCheck->setLogger($mockedLogger);

        $response = $httpClientHealthCheck->getServiceStatus();

        // then
        $expectedResponse = new HealthCheckResponse(
            500,
            'Request failed for service: sampleService',
            $request
        );
        $this->assertEquals($expectedResponse, $response);
    }
}
