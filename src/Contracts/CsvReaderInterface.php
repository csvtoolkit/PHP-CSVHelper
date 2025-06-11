<?php

namespace Phpcsv\CsvHelper\Contracts;

use FastCSVReader;
use SplFileObject;

/**
 * Interface for CSV reader implementations.
 *
 * Defines the contract for reading CSV files with support for different
 * underlying implementations (FastCSV extension or SplFileObject).
 */
interface CsvReaderInterface
{
    /**
     * Gets the underlying reader object.
     *
     * @return SplFileObject|FastCSVReader|null The reader object
     */
    public function getReader(): SplFileObject|FastCSVReader|null;

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    public function getConfig(): CsvConfigInterface;

    /**
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Number of records (excluding header if present)
     */
    public function getRecordCount(): ?int;

    /**
     * Rewinds the reader to the beginning of the data records.
     */
    public function rewind(): void;

    /**
     * Gets the current 0-based record position.
     *
     * @return int Current position (-1 if no record has been read, 0+ for actual positions)
     */
    public function getCurrentPosition(): int;

    /**
     * Gets the record at the current position without advancing.
     *
     * @return array|string|false Array of field values, or false if no record has been read
     */
    public function getRecord(): array|string|false;

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     */
    public function nextRecord(): array|false;

    /**
     * Gets the header row if headers are enabled.
     *
     * @return string|false|array Array of header field names, or false if headers disabled
     */
    public function getHeader(): string|false|array;

    /**
     * Checks if the CSV file contains any data records.
     *
     * @return bool True if file contains records, false otherwise
     */
    public function hasRecords(): bool;

    /**
     * Sets the CSV file path and resets the reader.
     *
     * @param string $source Path to the CSV file
     */
    public function setSource(string $source): void;

    /**
     * Gets the current CSV file path.
     *
     * @return string File path string
     */
    public function getSource(): string;

    /**
     * Updates the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    public function setConfig(CsvConfigInterface $config): void;
}
