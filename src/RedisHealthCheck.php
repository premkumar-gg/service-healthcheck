<?php
/**
 * {description}
 *
 * @author
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exception\InvalidOperationException;
use GuzzleHttp\Psr7\Response;
use Predis\Client;
use Predis\CommunicationException;

class RedisHealthCheck implements HealthCheck
{
    /** @var string */
    protected $serviceName;
    /**
     * @var Client
     */
    protected $client;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function __destruct()
    {
        $this->client->disconnect();
    }

    /**
     * Return collection of service statuses
     *
     * @return Response
     */
//    public function getServiceStatuses(): Response
//    {
//        $responses = [];
//        $responseData = [];
//
//        foreach ($this->clients as $client) {
//            /** @var Client $client */
//            $client->set('test-message', 'YES');
//            $value = $client->get('test-message');
//
//            if ('YES' === $value) {
//                $response = new HealthCheckResponse(
//                    200,
//                    'Message successfully stored and retrieved'
//                );
//                $responses[] = $response;
//                $responseData[] = $response->toArray();
//
//                continue;
//            }
//
//            $response = new HealthCheckResponse(
//                500,
//                'Failed to store and retrieve message'
//            );
//            $responses[] = $response;
//            $responseData[] = $response->toArray();
//        }
//
//        return new Response(
//            (new WorstCaseStatusCode())->getWorstCaseStatusCode($responses),
//            [],
//            json_encode($responseData)
//        );
//    }

    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        if (!isset($this->client)) {
            throw new InvalidOperationException(
                '$client is not set. Use setClient method to set the client before calling getServiceStatus.'
            );
        }

        if (!isset($this->request)) {
            throw new InvalidOperationException(
                '$request is not set. Use setRequest method to set the request before calling getServiceStatus.'
            );
        }

        $value = '';

        $this->client->set('test-message', 'YES');

        if ($this->client->exists('test-message')) {
            $value = $this->client->get('test-message');
        }

        if ('YES' === $value) {
            return new HealthCheckResponse(
                200,
                'Message successfully stored and retrieved'
            );
        }

        return new HealthCheckResponse(
            500,
            'Fatal error checking service: ' . $this->serviceName
        );
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}
