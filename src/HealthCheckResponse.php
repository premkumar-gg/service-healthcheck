<?php
/**
 * Response for health check
 *
 * @author Ian H <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

class HealthCheckResponse
{
    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var string
     */
    protected $data;

    public function __construct(int $statusCode, string $data)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
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
        return [
            'status' => $this->statusCode,
            'data' => $this->data,
        ];
    }
}
