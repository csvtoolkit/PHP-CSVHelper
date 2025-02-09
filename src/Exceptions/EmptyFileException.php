<?php

namespace Phpcsv\CsvHelper\Exceptions;

/**
 * Exception thrown when attempting to read an empty CSV file
 */
class EmptyFileException extends CsvReaderException
{
    /**
     * @param  string  $filePath  The path to the empty file
     */
    public function __construct(string $filePath)
    {
        parent::__construct("File is empty: {$filePath}");
    }
}
