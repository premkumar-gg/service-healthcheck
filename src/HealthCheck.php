<?php
/**
 * Interface for a service health check
 *
 * @author Ian H. <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

interface HealthCheck
{
    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse;
}
