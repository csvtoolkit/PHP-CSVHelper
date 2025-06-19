<?php

namespace CsvToolkit\Contracts;

/**
 * Interface for CSV reader implementations.
 *
 * Defines the core contract for reading CSV files that both
 * FastCSV extension and SplFileObject implementations can support.
 */
interface CsvReaderInterface
{
    /**
     * Gets the next record from the CSV file.
     *
     * @return array|false Array of field values, or false if no more records
     */
    public function nextRecord(): array|false;

    /**
     * Gets the current record without advancing the position.
     *
     * @param int|null $position Optional position to get record from
     * @return array|false Array of field values, or false if no record
     */
    public function getRecord(?int $position = null): array|false;

    /**
     * Seeks to a specific record position.
     *
     * @param int $position Zero-based record position
     * @return array|false Array of field values, or false if position invalid
     */
    public function seek(int $position): array|false;

    /**
     * Rewinds the reader to the beginning.
     */
    public function rewind(): void;

    /**
     * Gets the current position.
     *
     * @return int Current zero-based position
     */
    public function getCurrentPosition(): int;

    /**
     * Checks if there are more records to read.
     *
     * @return bool True if more records available
     */
    public function hasNext(): bool;

    /**
     * Checks if the CSV has any records.
     *
     * @return bool True if records exist
     */
    public function hasRecords(): bool;

    /**
     * Gets the header row if available.
     *
     * @return array|false Array of header names, or false if no header
     */
    public function getHeader(): array|false;

    /**
     * Gets the total number of records.
     *
     * @return int|null Total record count, or null if unknown
     */
    public function getRecordCount(): ?int;

    /**
     * Sets the source file path.
     *
     * @param string $source Path to CSV file
     */
    public function setSource(string $source): void;

    /**
     * Gets the current source file path.
     *
     * @return string File path
     */
    public function getSource(): string;
}
