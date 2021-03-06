<?php
/**
 * Return a worst case http status from a collection of statuses
 *
 * @author Ian.H <ian@ianh.io>
 */

namespace Giffgaff\ServiceHealthCheck;

/**
 * Class WorstCaseStatusCode
 *
 * @package Giffgaff\ServiceHealthCheck
 */
class WorstCaseStatusCode
{
    /** @var array */
    protected static $statusSeverityMaps = [
        '5*',
        '4*',
        '200',
    ];

    /**
     * @param array $responses
     *
     * @return int
     */
    public function getWorstCaseStatusCode(array $responses): int
    {
        $worstStatusCode = 200;
        $worstCaseIndex = \count(self::$statusSeverityMaps) - 1;

        foreach ($responses as $response) {
            /** @var HealthCheckResponse $response */
            $statusCode = $response->getStatusCode();

            switch (((string)$statusCode)[0]) {
                case '5':
                    $code = '5*';
                    break;
                case '4':
                    $code = '4*';
                    break;
                default:
                    $code = '200';
                    break;
            }

            foreach (self::$statusSeverityMaps as $index => $indexValue) {
                if ($index < $worstCaseIndex && ($code === (string)$indexValue)) {
                    $worstCaseIndex = $index;
                    $worstStatusCode = $statusCode;
                    break;
                }
            }
        }

        return $worstStatusCode;
    }
}
