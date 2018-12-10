<?php
/**
 * Health check for any simple cache testing operations of save, load, and remove
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Interfaces\Cache;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;

/**
 * Class CacheHealthCheck
 *
 * Health check for a cache client
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class CacheHealthCheck implements HealthCheck
{
    /** @var string */
    protected $serviceName;
    /** @var Cache */
    protected $cache;
    /** @var bool */
    protected $debugMode = false;

    /**
     * CacheHealthCheck constructor.
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
     * @param $cache Cache
     */
    public function setCache(Cache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Returns the status of a service
     *
     * @return HealthCheckResponse
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        try {
            $id = 'TEST-DATA';
            $this->cache->save('YES', $id);
            $result = $this->cache->load($id);
            $this->cache->remove($id);

            if ('YES' === $result) {
                return new HealthCheckResponse(
                    200,
                    'Able to write and read some data in ' . $this->serviceName,
                    $this->debugMode
                );
            }

            return new HealthCheckResponse(
                500,
                'Something went wrong while writing into ' . $this->serviceName . '. ' .
                'The value read is not the same as the written.',
                $this->debugMode
            );
        } catch (\Exception $e) {
            return new HealthCheckResponse(
                500,
                'Fatal error while testing ' . $this->serviceName . '.',
                $this->debugMode
            );
        }
    }
}
