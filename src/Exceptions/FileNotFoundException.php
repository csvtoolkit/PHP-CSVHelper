<?php

namespace Phpcsv\CsvHelper\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a CSV file is not found
 */
class FileNotFoundException extends RuntimeException
{
    /**
     * @param  string  $filePath  The path to the file that was not found
     */
    public function __construct(string $filePath, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('File does not exist: %s', $filePath);
        parent::__construct($message, $code, $previous);
    }
}
