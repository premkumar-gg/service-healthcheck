<?php

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class ServiceHealthCheck
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class ServiceHealthCheck
{
    /** @var array */
    protected $services;

    /**
     * ServiceHealthCheck constructor.
     *
     * @param array $services
     */
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
            if (!($healthCheck instanceof HealthCheckInterface)) {
                throw new InvalidOperationException(
                    'Service ' . $serviceName . ' does not have a valid HealthCheckInterface object'
                );
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
