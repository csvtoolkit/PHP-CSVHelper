<?php

namespace CsvToolkit\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a required directory is not found
 */
class DirectoryNotFoundException extends RuntimeException
{
    public function __construct(string $directoryPath, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Directory does not exist: %s', $directoryPath), $code, $previous);
    }
}
