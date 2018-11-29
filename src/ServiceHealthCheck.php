<?php

namespace Giffgaff\ServiceHealthCheck;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class ServiceHealthCheck
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;
    /**
     * @var array
     */
    protected $services;

    public function __construct(\GuzzleHttp\Client $client, array $services)
    {
        $this->client = $client;
        $this->services = $services;
    }

    /**
     * Check then health status of a remote service API
     *
     * @param string $serviceName
     * @param string $serviceUrl
     *
     * @return HealthCheckResponse
     */
    protected function checkService(string $serviceName, string $serviceUrl): HealthCheckResponse
    {
        try {
            $response = $this->client->get($serviceUrl);

            if (null !== $response) {
                return new HealthCheckResponse($response->getStatusCode(), $response->getBody()->getContents());
            }

            return new HealthCheckResponse(500, 'Fatal error checking service: ' . $serviceName);
        } catch (RequestException $exception) {
            return new HealthCheckResponse(500, 'Request failed for service: ' . $serviceName);
        }
    }

    /**
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response
    {
        $responses = [];
        $responseData = [];

        foreach ($this->services as $serviceName => $serviceUrl) {
            $response = $this->checkService($serviceName, $serviceUrl);
            $responses[$serviceName] = $response;
            $responseData[$serviceName] = $response->toArray();
        }

        return new Response(
            (new WorstCaseStatusCode())->getWorstCaseStatusCode($responses),
            [],
            json_encode($responseData)
        );
    }
}
