<?php
/**
 * Memcached HealthCheck client
 *
 * @author Ian.H <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;
use GuzzleHttp\Psr7\Response;
use Memcached;

class MemcachedHealthCheck implements HealthCheck
{
    /** @var Memcached */
    protected $client;
    /**
     * @var string
     */
    protected $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * Returns the status of a service
     *
     * @return Response
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        if (null === $this->client) {
            throw new InvalidOperationException(
                '$client is not set. Use setClient method to set the client before calling getServiceStatus.'
            );
        }

        /** @var Memcached $client */
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

    public function setClient(Memcached $client): void
    {
        $this->client = $client;
    }
}
