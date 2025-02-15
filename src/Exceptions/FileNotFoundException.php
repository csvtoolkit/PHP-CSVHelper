<?php

namespace Phpcsv\CsvHelper\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a CSV file is not found
 */
class FileNotFoundException extends RuntimeException
{
    public function __construct(string $filePath, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('File does not exist: %s', $filePath), $code, $previous);
    }
}
