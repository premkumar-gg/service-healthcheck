<?php
/**
 * {description}
 *
 * @author
 */

namespace Giffgaff\ServiceHealthCheck;

class WorstCaseStatusCode
{
    /**
     * @var array
     */
    protected static $statusCodeSeverityMap = [
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
        $worstCaseIndex = \count(self::$statusCodeSeverityMap) - 1;

        foreach ($responses as $response) {
            $statusCode = $response['status'];

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

            foreach (self::$statusCodeSeverityMap as $index => $indexValue) {
                if ($index < $worstCaseIndex && ($code === (string)self::$statusCodeSeverityMap[$index])) {
                    $worstCaseIndex = $index;
                    $worstStatusCode = $statusCode;
                    break;
                }
            }
        }

        return $worstStatusCode;
    }
}
