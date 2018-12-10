<?php
namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * Health check for a http client
 */
class HttpClientHealthCheck implements HealthCheck
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
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
            $requestOptions = [];

            if (!empty($this->request->getHeaders())) {
                $requestOptions['headers'] = $this->request->getHeaders();
            }

            if (!empty($this->request->getBody())) {
                $requestOptions['body'] = $this->request->getHeaders();
            }

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
                    $response->getBody()->getContents()
                );
            }

            return new HealthCheckResponse(
                500,
                'Fatal error checking service: ' . $this->serviceName
            );
        } catch (RequestException $exception) {
            return new HealthCheckResponse(
                500,
                'Request failed for service: ' . $this->serviceName
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
