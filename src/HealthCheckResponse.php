<?php
/**
 * Response for health check
 *
 * @author Ian H <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

use GuzzleHttp\Psr7\Request;

class HealthCheckResponse
{
    /** @var string */
    protected const STATUS_UP = 'UP';
    /** @var string */
    protected const STATUS_DOWN = 'DOWN';
    /** @var int */
    protected $statusCode;
    /** @var string */
    protected $data;
    /** @var bool */
    protected $debugMode = false;
    /** @var Request */
    protected $request;
    /** @var string */
    protected $logFile;

    /**
     * HealthCheckResponse constructor.
     *
     * @param int $statusCode
     * @param string $data
     * @param bool $debugMode
     * @param Request|null $request
     */
    public function __construct(
        int $statusCode,
        string $data,
        Request $request = null
    ) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if ($this->debugMode) {
            $data = $this->data;
        } elseif (($this->statusCode >= 200) && ($this->statusCode <= 226)) {
            $data = self::STATUS_UP;
        } else {
            $data = self::STATUS_DOWN;
        }

        return [
            'status' => $this->statusCode,
            'data' => $data,
        ];
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * @param bool $debugMode
     */
    public function setDebugMode(bool $debugMode): void
    {
        $this->debugMode = $debugMode;
    }
}
