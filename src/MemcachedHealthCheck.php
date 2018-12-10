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

/**
 * Class MemcachedHealthCheck
 *
 * Memcached health check client
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class MemcachedHealthCheck implements HealthCheck
{
    /** @var Memcached */
    protected $client;
    /** @var string */
    protected $serviceName;
    /** @var bool */
    protected $debugMode = false;

    /**
     * MemcachedHealthCheck constructor.
     *
     * @param string $serviceName
     * @param bool $debugMode
     */
    public function __construct(string $serviceName, bool $debugMode = false)
    {
        $this->serviceName = $serviceName;
        $this->debugMode = $debugMode;
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
                'Message successfully stored and retrieved for: ' . $this->serviceName,
                $this->debugMode
            );
        }

        return new HealthCheckResponse(
            500,
            'Failed to store and retrieve message for: ' . $this->serviceName,
            $this->debugMode
        );
    }

    /**
     * @param Memcached $client
     */
    public function setClient(Memcached $client): void
    {
        $this->client = $client;
    }
}
