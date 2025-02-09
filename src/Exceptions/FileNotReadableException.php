<?php

namespace Phpcsv\CsvHelper\Exceptions;

/**
 * Exception thrown when a CSV file exists but is not readable
 */
class FileNotReadableException extends CsvReaderException
{
    /**
     * @param  string  $filePath  The path to the unreadable file
     */
    public function __construct(string $filePath)
    {
        parent::__construct("File is not readable: {$filePath}");
    }
}
