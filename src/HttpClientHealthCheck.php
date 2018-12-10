<?php
/**
 * Health check client for HTTP requests
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * Class HttpClientHealthCheck
 *
 * Health check for an HTTP client
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class HttpClientHealthCheck implements HealthCheck
{
    /** @var Client */
    protected $client;
    /** @var Request */
    protected $request;
    /** @var string  */
    protected $serviceName;
    /** @var bool */
    protected $debugMode = false;

    public function __construct(string $serviceName, bool $debugMode = false)
    {
        $this->serviceName = $serviceName;
        $this->debugMode = $debugMode;
    }

    /**
     * Returns the status of a Http service
     *
     * @return HealthCheckResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        if (null === $this->client) {
            throw new InvalidOperationException(
                '$client is not set. Use setClient method to set the client before calling getServiceStatus.'
            );
        }

        if (null === $this->request) {
            throw new InvalidOperationException(
                '$request is not set. Use setRequest method to set the request before calling getServiceStatus.'
            );
        }

        try {
            $response = $this->client->request(
                $this->request->getMethod(),
                $this->request->getUri(),
                [
                    'headers' => $this->request->getHeaders(),
                    'body' => $this->request->getBody()->getContents()
                ]
            );

            if (null !== $response) {
                return new HealthCheckResponse(
                    $response->getStatusCode(),
                    $response->getBody()->getContents(),
                    $this->debugMode
                );
            }

            return new HealthCheckResponse(
                500,
                'Fatal error checking service: ' . $this->serviceName,
                $this->debugMode
            );
        } catch (RequestException $exception) {
            return new HealthCheckResponse(
                500,
                'Request failed for service: ' . $this->serviceName,
                $this->debugMode
            );
        }
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
