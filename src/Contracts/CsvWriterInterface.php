<?php

namespace CsvToolkit\Contracts;

use FastCSVWriter;
use SplFileObject;

/**
 * Interface for CSV writer implementations.
 *
 * Defines the contract for writing CSV files with support for different
 * underlying implementations (FastCSV extension or SplFileObject).
 */
interface CsvWriterInterface
{
    /**
     * Gets the underlying writer object.
     *
     * @return SplFileObject|FastCSVWriter|null The writer object
     */
    public function getWriter(): SplFileObject|FastCSVWriter|null;

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    public function getConfig(): CsvConfigInterface;

    /**
     * Writes a single record to the CSV file.
     *
     * @param array $data Array of field values to write
     */
    public function write(array $data): void;

    /**
     * Sets the output file path.
     *
     * @param string $target File path for CSV output
     */
    public function setTarget(string $target): void;

    /**
     * Gets the current output file path.
     *
     * @return string File path string
     */
    public function getTarget(): string;
}
