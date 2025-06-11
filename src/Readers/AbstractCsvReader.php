<?php

namespace Phpcsv\CsvHelper\Readers;

use FastCSVReader;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvReaderInterface;
use SplFileObject;

/**
 * Abstract base class for CSV readers.
 *
 * Provides common functionality and properties for CSV reader implementations.
 * Concrete implementations must handle the specific reading logic for their
 * underlying libraries (e.g., FastCSV extension or SplFileObject).
 */
abstract class AbstractCsvReader implements CsvReaderInterface
{
    /**
     * The CSV configuration object.
     */
    protected ?CsvConfigInterface $config = null;

    /**
     * Cached header row data.
     */
    protected ?array $header = null;

    /**
     * Total number of records in the CSV file.
     */
    protected ?int $recordCount = null;

    /**
     * Current position in the CSV file (-1 means no record read yet).
     */
    protected int $position = -1;

    /**
     * Cache for the current record to avoid re-reading.
     */
    protected ?array $cachedRecord = null;

    /**
     * The underlying reader object (SplFileObject or FastCSVReader).
     */
    protected SplFileObject|FastCSVReader|null $reader = null;

    /**
     * Creates a new CSV reader instance.
     *
     * @param string|null $source Optional file path to CSV file
     * @param CsvConfigInterface|null $config Optional configuration object
     */
    abstract public function __construct(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    );

    /**
     * Gets the underlying reader object.
     *
     * @return SplFileObject|FastCSVReader|null The reader object
     */
    abstract public function getReader(): SplFileObject|FastCSVReader|null;

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    abstract public function getConfig(): CsvConfigInterface;

    /**
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Number of records (excluding header if present)
     */
    abstract public function getRecordCount(): ?int;

    /**
     * Rewinds the reader to the beginning of the data records.
     *
     * Resets position to -1 (no record read state) and clears cached data.
     * Subclasses should call parent::rewind() and perform any additional
     * reset operations specific to their implementation (e.g., resetting
     * file pointers, re-caching headers, clearing internal state).
     */
    public function rewind(): void
    {
        $this->position = -1;  // Reset to -1 (no record read)
        $this->cachedRecord = null;  // Clear cache
    }

    /**
     * Gets the current 0-based record position.
     *
     * @return int Current position (-1 if no record has been read, 0+ for actual positions)
     */
    abstract public function getCurrentPosition(): int;

    /**
     * Gets the record at the current position without advancing.
     *
     * @return array|false Array of field values, or false if no record has been read
     */
    abstract public function getRecord(): array|false;

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     */
    abstract public function nextRecord(): array|false;

    /**
     * Gets the header row if headers are enabled.
     *
     * @return array|false Array of header field names, or false if headers disabled
     */
    abstract public function getHeader(): array|false;

    /**
     * Checks if the CSV file contains any data records.
     *
     * @return bool True if file contains records, false otherwise
     */
    abstract public function hasRecords(): bool;

    /**
     * Sets the CSV file path and resets the reader.
     *
     * @param string $source Path to the CSV file
     */
    abstract public function setSource(string $source): void;

    /**
     * Gets the current CSV file path.
     *
     * @return string File path string
     */
    abstract public function getSource(): string;

    /**
     * Updates the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    abstract public function setConfig(CsvConfigInterface $config): void;

    /**
     * Seeks to a specific 0-based record position.
     *
     * @param int $position Zero-based position to seek to
     * @return array|false Array of field values at the position, or false if invalid position
     */
    abstract public function seek(int $position): array|false;
}
