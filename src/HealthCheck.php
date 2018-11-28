<?php
/**
 * {description}
 *
 * @author
 */

namespace Giffgaff\ServiceHealthCheck;

use GuzzleHttp\Psr7\Response;

interface HealthCheck
{
    /**
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response;
}
