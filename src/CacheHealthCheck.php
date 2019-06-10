<?php
/**
 * Health check for any simple cache testing operations of save, load, and remove
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Interfaces\CacheInterface;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CacheHealthCheck
 *
 * Health check for a cache client
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class CacheHealthCheck implements HealthCheckInterface
{
    /** @var string */
    protected $serviceName;
    /** @var CacheInterface */
    protected $cache;
    /** @var bool */
    protected $debugMode = false;
    /** @var LoggerInterface */
    protected $logger;

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
     * @param $cache CacheInterface
     */
    public function setCache(CacheInterface $cache): void
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
            $transactionId = uniqid();
            $this->logger->info(
                "(HealthCheck)($transactionId): checking cache service {$this->serviceName}"
            );

            $id = 'TEST-DATA';
            $this->cache->save('YES', $id);
            $result = $this->cache->load($id);
            $this->cache->remove($id);

            if ('YES' === $result) {
                $this->logger->info(
                    "(HealthCheck)($transactionId): check for cache service {$this->serviceName} successful"
                );
                return new HealthCheckResponse(
                    200,
                    'Able to write and read some data in ' . $this->serviceName,
                    $this->debugMode
                );
            }

            $this->logger->info(
                "(HealthCheck)($transactionId): check for cache service {$this->serviceName} failed." .
                'The value read is not the same as the written.'
            );

            return new HealthCheckResponse(
                500,
                'Something went wrong while writing into ' . $this->serviceName . '. ' .
                'The value read is not the same as the written.',
                $this->debugMode
            );
        } catch (\Exception $e) {
            $this->logger->error(
                "(HealthCheck)($transactionId): check for cache service {$this->serviceName} failed.",
                [
                    'log_source' => __FILE__,
                    'exception_type' => 'Exception',
                    'exception' => $e
                ]
            );

            return new HealthCheckResponse(
                500,
                'Fatal error while testing ' . $this->serviceName . '.',
                $this->debugMode
            );
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
