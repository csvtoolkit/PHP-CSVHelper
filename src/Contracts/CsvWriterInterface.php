<?php

namespace CsvToolkit\Contracts;

/**
 * Interface for CSV writer implementations.
 *
 * Defines the core contract for writing CSV files that both
 * FastCSV extension and SplFileObject implementations can support.
 */
interface CsvWriterInterface
{
    /**
     * Writes a single record to the CSV file.
     *
     * @param array $data Array of field values to write
     */
    public function write(array $data): void;

    /**
     * Writes multiple records to the CSV file.
     *
     * @param array<int, array> $records Array of records to write
     */
    public function writeAll(array $records): void;

    /**
     * Sets the CSV headers.
     *
     * @param array $headers Array of header strings
     */
    public function setHeaders(array $headers): void;

    /**
     * Gets the CSV headers.
     *
     * @return array|null Array of header strings or null if not set
     */
    public function getHeaders(): ?array;

    /**
     * Sets the destination file path.
     *
     * @param string $destination Path to write CSV file
     */
    public function setDestination(string $destination): void;

    /**
     * Gets the current destination file path.
     *
     * @return string File path
     */
    public function getDestination(): string;

    /**
     * Manually flushes buffered data to disk.
     * This method is useful when auto-flush is disabled for performance reasons.
     * Call this periodically (e.g., every 1000 records) to ensure data is written.
     *
     * @return bool True on success, false on failure
     */
    public function flush(): bool;

    /**
     * Closes the writer and frees resources.
     */
    public function close(): void;
}
