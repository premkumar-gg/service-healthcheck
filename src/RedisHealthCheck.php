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
     * Return collection of service statuses
     *
     * @return Response
     */
    public function getServiceStatuses(): Response
    {
        try {
            $redis = new Client([
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ]);

            $redis->set('test-message', 'YES');
            $value = $redis->get('test-message');

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
        } catch (\Exception $exception) {
            return new Response([
                503,
                [],
                json_encode(['data' => 'Failed to make connection to redis server']),
            ]);
        }
    }
}
