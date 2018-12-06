<?php
/**
 * Health check for any simple cache testing operations of save, load, and remove
 */

namespace Giffgaff\ServiceHealthCheck;


use Giffgaff\ServiceHealthCheck\Interfaces\Cache;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheck;

class CacheHealthCheck implements HealthCheck
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @param $aCache Cache
     */
    public function setCache(Cache $aCache)
    {
        $this->cache = $aCache;
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
                return new HealthCheckResponse(200, 'Able to write and read some data in ' . $this->serviceName);
            }

            return new HealthCheckResponse(
                500,
                "Something went wrong while writing into {$this->serviceName}. The value read is not the same as the written."
            );
        } catch (\Exception $e) {
            return new HealthCheckResponse(500, "Fatal error while testing {$this->serviceName}.");
        }
    }
}
