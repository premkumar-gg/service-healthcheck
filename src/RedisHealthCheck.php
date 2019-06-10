<?php
/**
 * Redis HealthCheckInterface client
 *
 * @author Ian.H <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;

class RedisHealthCheck implements HealthCheckInterface
{
    /** @var string */
    protected $serviceName;
    /** @var Client */
    protected $client;
    /** @var bool */
    protected $debugMode = false;

    /**
     * RedisHealthCheck constructor.
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
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        // TODO: Implement setLogger() method.
    }
}
