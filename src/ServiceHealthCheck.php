<?php

namespace Icawebdesign\ServiceHealthCheck;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Icawebdesign\ServiceHealthCheck\Exception\ConfigNotFoundException;
use Icawebdesign\ServiceHealthCheck\Exception\InvalidConfigException;
use Symfony\Component\Yaml\Yaml;

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
     * @return array
     */
    protected function checkService(string $serviceName, string $serviceUrl): array
    {
        try {
            $response = $this->client->get($serviceUrl);

            if (null !== $response) {
                return [
                    'status' => $response->getStatusCode(),
                    'data' => $response->getBody()->getContents(),
                ];
            }

            return [
                'status' => 500,
                'data' => 'Fatal error checking service: ' . $serviceName,
            ];
        } catch (RequestException $exception) {
            return [
                'status' => 500,
                'data' => 'Request failed for service: ' . $serviceName,
            ];
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

        foreach ($this->services as $serviceName => $serviceUrl) {
            $responses[$serviceName] = $this->checkService($serviceName, $serviceUrl);
        }

        return new Response(
            (new WorstCaseStatusCode())->getWorstCaseStatusCode($responses),
            [],
            json_encode($responses)
        );
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
            throw new ConfigNotFoundException('Config file [' . $configFile . '] not found');
        }

        $services = Yaml::parseFile($configFile);

        if (null === $services) {
            throw new InvalidConfigException('Config file [' . $configFile . '] is invalid');
        }

        if (array_key_exists('services', $services)) {
            return $services['services'];
        }

        return [];
    }
}
