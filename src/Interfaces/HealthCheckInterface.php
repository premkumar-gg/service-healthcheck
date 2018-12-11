<?php
/**
 * Interface for a service health check
 *
 * @author Ian H. <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck\Interfaces;

use Giffgaff\ServiceHealthCheck\HealthCheckResponse;

interface HealthCheckInterface
{
    public function __construct(string $serviceName, bool $debugMode);

    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse;
}
