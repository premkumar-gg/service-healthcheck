<?php
/**
 * {description}
 *
 * @author
 */

use PHPUnit\Framework\TestCase;

class WorstCaseStatusCodeTest extends TestCase
{
    /** @test */
    public function returns200StatusCodeFromSuccessfulResponses(): void
    {
        $response = [
            ['status' => 200, 'data' => 'test'],
            ['status' => 200, 'data' => 'test1'],
            ['status' => 200, 'data' => 'test2'],
        ];
        $worstCaseStatusCode = new \Giffgaff\ServiceHealthCheck\WorstCaseStatusCode();
        $this->assertEquals(200, $worstCaseStatusCode->getWorstCaseStatusCode($response));
    }

    /** @test */
    public function returns400RangeStatusCodeFromResponses(): void
    {
        $response = [
            ['status' => 200, 'data' => 'test'],
            ['status' => 200, 'data' => 'test1'],
            ['status' => 403, 'data' => 'test2'],
        ];
        $worstCaseStatusCode = new \Giffgaff\ServiceHealthCheck\WorstCaseStatusCode();
        $this->assertEquals(403, $worstCaseStatusCode->getWorstCaseStatusCode($response));
    }

    /** @test */
    public function returns500RangeStatusCodeFromResponses(): void
    {
        $response = [
            ['status' => 200, 'data' => 'test'],
            ['status' => 500, 'data' => 'test1'],
            ['status' => 503, 'data' => 'test2'],
        ];
        $worstCaseStatusCode = new \Giffgaff\ServiceHealthCheck\WorstCaseStatusCode();
        $this->assertEquals(500, $worstCaseStatusCode->getWorstCaseStatusCode($response));
    }
}
