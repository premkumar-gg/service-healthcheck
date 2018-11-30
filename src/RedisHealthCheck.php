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

class RedisHealthCheck implements HealthCheck
{
    /**
     * @var string
     */
    protected $serviceName;

    /** @var Client */
    protected $client;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        if (null === $this->client) {
            throw new InvalidOperationException(
                '$client is not set. Use setClient method to set the client before calling getServiceStatus.'
            );
        }

        $this->client->set('test-message', 'YES');
        $value = $this->client->get('test-message');

        if ('YES' === $value) {
            return new HealthCheckResponse(
                200,
                'Message successfully stored and retrieved for: ' . $this->serviceName
            );
        }

        return new HealthCheckResponse(
            500,
            'Failed to store and retrieve message for: ' . $this->serviceName
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
