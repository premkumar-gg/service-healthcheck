<?php
/**
 * {description}
 *
 * @author
 */

namespace Tests;

use Giffgaff\ServiceHealthCheck\HealthCheckResponse;
use Giffgaff\ServiceHealthCheck\WorstCaseStatusCode;
use PHPUnit\Framework\TestCase;

class WorstCaseStatusCodeTest extends TestCase
{
    /** @test */
    public function returns200StatusCodeFromSuccessfulResponses(): void
    {
        $responses = [
            new HealthCheckResponse(200, 'test'),
            new HealthCheckResponse(200, 'test'),
            new HealthCheckResponse(200, 'test'),
        ];
        $worstCaseStatusCode = new WorstCaseStatusCode();
        $this->assertEquals(200, $worstCaseStatusCode->getWorstCaseStatusCode($responses));
    }

    /** @test */
    public function returns400RangeStatusCodeFromResponses(): void
    {
        $responses = [
            new HealthCheckResponse(200, 'test'),
            new HealthCheckResponse(200, 'test'),
            new HealthCheckResponse(403, 'test'),
        ];
        $worstCaseStatusCode = new WorstCaseStatusCode();
        $this->assertEquals(403, $worstCaseStatusCode->getWorstCaseStatusCode($responses));
    }

    /** @test */
    public function returns500RangeStatusCodeFromResponses(): void
    {
        $responses = [
            new HealthCheckResponse(200, 'test'),
            new HealthCheckResponse(500, 'test'),
            new HealthCheckResponse(503, 'test'),
        ];
        $worstCaseStatusCode = new WorstCaseStatusCode();
        $this->assertEquals(500, $worstCaseStatusCode->getWorstCaseStatusCode($responses));
    }
}
