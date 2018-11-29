<?php

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exception\InvalidOperationException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class ServiceHealthCheck
{
    /**
     * @var array
     */
    protected $services;

    public function __construct(array $services)
    {
        $this->services = $services;
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

        foreach ($this->services as $serviceName => $healthCheck) {
            if (!($healthCheck instanceof HealthCheck)) {
                throw new InvalidOperationException('Service ' . $serviceName . ' does not have a valid HealthCheck object');
            }

            $response = $healthCheck->getServiceStatus();
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
