<?php
/**
 * Health check client for HTTP requests
 */

namespace Giffgaff\ServiceHealthCheck;

use Giffgaff\ServiceHealthCheck\Exceptions\InvalidOperationException;
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

/**
 * Class HttpClientHealthCheck
 *
 * Health check for an HTTP client
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class HttpClientHealthCheck implements HealthCheckInterface
{
    /** @var ClientInterface */
    protected $client;
    /** @var Request */
    protected $request;
    /** @var string  */
    protected $serviceName;
    /** @var bool */
    protected $debugMode = false;
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * Returns the status of a Http service
     *
     * @return HealthCheckResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getServiceStatus(): HealthCheckResponse
    {
        if (null === $this->client) {
            throw new InvalidOperationException(
                '$client is not set. Use setClient method to set the client before calling getServiceStatus.'
            );
        }

        if (null === $this->request) {
            throw new InvalidOperationException(
                '$request is not set. Use setRequest method to set the request before calling getServiceStatus.'
            );
        }

        try {
            $transactionId = uniqid();

            $this->logger->info(
                "(HealthCheck)($transactionId): making a http client request for service {$this->serviceName}",
                [
                    'end_point' => $this->request->getUri()->__toString(),
                    'method' => $this->request->getMethod()
                ]
            );

            $response = $this->client->request(
                $this->request->getMethod(),
                $this->request->getUri(),
                [
                    'headers' => $this->request->getHeaders(),
                    'body' => $this->request->getBody()->getContents()
                ]
            );

            if (null !== $response) {
                $this->logger->info(
                    "(HealthCheck)($transactionId): received a positive http client response for service {$this->serviceName}"
                );

                return new HealthCheckResponse(
                    $response->getStatusCode(),
                    $response->getBody()->getContents(),
                    $this->request
                );
            }

            $this->logger->error(
                "(HealthCheck)($transactionId): received an unexpected null http client response for service {$this->serviceName}"
            );
            return new HealthCheckResponse(
                500,
                'Fatal error checking service: ' . $this->serviceName,
                $this->request
            );
        } catch (RequestException $exception) {
            $this->logRequestException($transactionId, $exception);
            $response = $exception->getResponse();
            $statusCode = $response !== null ? $response->getStatusCode() : 500;
            return $this->failService($statusCode);
        } catch (\Exception $exception) {
            $this->logRequestException($transactionId, $exception);
            return $this->failService(500);
        }
    }

    protected function failService(int $responseCode)
    {
        return new HealthCheckResponse(
            $responseCode,
            'Request failed for service: ' . $this->serviceName,
            $this->request
        );
    }

    protected function logRequestException(string $transactionId, \Exception $exception)
    {
        $this->logger->error(
            "(HealthCheck)($transactionId): Request failed for service {$this->serviceName}",
            [
                'log_source' => __FILE__,
                'exception' => $exception
            ]
        );
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
