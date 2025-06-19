<?php

namespace CsvToolkit\Exceptions;

use Exception;

/**
 * Exception thrown when a file is not writable.
 */
class FileNotWritableException extends Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct("File is not writable: {$filePath}");
    }
}
