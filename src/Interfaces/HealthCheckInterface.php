<?php
/**
 * Interface for a service health check
 *
 * @author Ian H. <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck\Interfaces;

use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Psr\Log\LoggerInterface;

interface HealthCheckInterface
{
    public function __construct(string $serviceName);

    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse;

    public function setLogger(LoggerInterface $logger): void;
}
