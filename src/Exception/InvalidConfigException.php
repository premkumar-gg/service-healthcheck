<?php
/**
 * Invalid Config exception handler
 *
 * @author Ian <ian@ianh.io>
 * @since 27/11/2018
 */

namespace Icawebdesign\ServiceHealthCheck\Exception;

class InvalidConfigException extends \RuntimeException implements Exception
{
    public function __construct(string $message = null, int $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown ' . \get_class($this));
        }

        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return sprintf(
            "%s %s in %s(%s)\n%s",
            \get_class($this),
            $this->message,
            $this->file,
            $this->line,
            $this->getTraceAsString()
        );
    }
}
