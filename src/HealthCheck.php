<?php

namespace Icawebdesign\ServiceHealthCheck;

use GuzzleHttp\Psr7\Response;
use Icawebdesign\ServiceHealthCheck\Exception\ConfigNotFoundException;
use Symfony\Component\Yaml\Yaml;

class HealthCheck
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
     * @param string $serviceUrl
     *
     * @return array
     */
    protected function checkService(string $serviceUrl): array
    {
        $response = $this->client->get($serviceUrl);

        if (null !== $response) {
            $data = [
                'status' => $response->getStatusCode(),
                'data' => $response->getBody()->getContents(),
            ];

            return $data;
        }

        return [
            'status' => 500,
            'data' => null,
        ];
    }

    /**
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response
    {
        $responses = [];

        foreach ($this->services as $serviceName => $serviceUrl) {
            $responses[$serviceName] = $this->checkService($serviceUrl);
        }

        return new Response(200 , [], json_encode($responses));
    }

    /**
     * Load services config
     *
     * @param string $configFile
     *
     * @return array
     */
    public function loadConfig(string $configFile): array
    {
        if (!file_exists($configFile)) {
            throw new ConfigNotFoundException('Config file not found');
        }

        $services = Yaml::parseFile($configFile);

        if (array_key_exists('services', $services)) {
            return $services['services'];
        }

        return [];
    }
}