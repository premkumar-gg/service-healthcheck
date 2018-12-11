<?php

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ServiceHealthCheck constructor.
     *
     * @param array $services
     */
    public function __construct(array $services, LoggerInterface $logger = null)
    {
        $this->services = $services;
        $this->logger = $logger;
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

            if ($this->logger !== null) {
                $healthCheck->setLogger($this->logger);
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
