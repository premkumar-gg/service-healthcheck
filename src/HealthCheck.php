<?php
/**
 * {description}
 *
 * @author
 */

namespace Icawebdesign\ServiceHealthCheck;

use GuzzleHttp\Psr7\Response;

interface HealthCheck
{
    /**
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response;

    /**
     * Load services config
     *
     * @param string $configFile
     * @param string $section
     *
     * @return array
     */
    public function loadConfig(string $configFile, string $section): array;
}
