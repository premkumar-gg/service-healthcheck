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
    protected $redis;

    public function __construct(string $host, int $port = 6379, string $scheme = 'tcp')
    {
        try {
            $this->redis = new Client([
                'scheme' => $scheme,
                'host' => $host,
                'port' => $port,
            ]);
        } catch (\RuntimeException $exception) {
            return new Response([
                503,
                [],
                json_encode(['data' => 'Failed to make connection to redis server']),
            ]);
        }
    }
    /**
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response
    {
        $this->redis->set('test-message', 'YES');
        $value = $this->redis->get('test-message');

        if ('YES' === $value) {
            return new Response([
                200,
                [],
                json_encode(['data' => 'Message successfully stored and retrieved']),
            ]);
        }

        return new Response([
            500,
            [],
            json_encode(['data' => 'Failed to store and retrieve message']),
        ]);
    }
}
