<?php
/**
 * {description}
 *
 * @author
 */

namespace Giffgaff\ServiceHealthCheck;

use GuzzleHttp\Psr7\Response;
use Predis\Client;

class RedisHealthCheck implements HealthCheck
{
    /**
     * @var array
     */
    protected $clients;

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    public function __destruct()
    {
        foreach ($this->clients as $client) {
            /** @var Client $client */
            $client->disconnect();
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
        $responseData = [];

        foreach ($this->clients as $client) {
            /** @var Client $client */
            $client->set('test-message', 'YES');
            $value = $client->get('test-message');

            if ('YES' === $value) {
                $response = new HealthCheckResponse(
                    200,
                    'Message successfully stored and retrieved'
                );
                $responses[] = $response;
                $responseData[] = $response->toArray();

                continue;
            }

            $response = new HealthCheckResponse(
                500,
                'Failed to store and retrieve message'
            );
            $responses[] = $response;
            $responseData[] = $response->toArray();
        }

        return new Response(
            (new WorstCaseStatusCode())->getWorstCaseStatusCode($responses),
            [],
            json_encode($responseData)
        );
    }
}
