<?php

namespace CsvToolkit\Readers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;
use Exception;
use FastCSVReader;
use RuntimeException;
use SplFileObject;

/**
 * CSV Reader implementation using SplFileObject
 *
 * This class provides functionality to read CSV files using PHP's built-in SplFileObject.
 * It supports custom delimiters, enclosures, and escape characters.
 *
 * This implementation is designed to match FastCSV extension behavior exactly.
 */
class SplCsvReader extends AbstractCsvReader
{
    /**
     * Creates a new SplFileObject-based CSV reader instance.
     *
     * @param string|null $source Optional file path to CSV file
     * @param CsvConfigInterface|null $config Optional configuration object
     */
    public function __construct(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    ) {
        $this->config = $config ?? new CsvConfig();

        if ($source !== null) {
            $this->setSource($source);
        }
    }

    /**
     * Gets the underlying SplFileObject instance.
     *
     * @return SplFileObject|FastCSVReader|null The SplFileObject instance
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     */
    public function getReader(): null|SplFileObject|FastCSVReader
    {
        if (! $this->reader instanceof SplFileObject) {
            $this->setReader();
        }

        return $this->reader;
    }

    /**
     * Initializes the SplFileObject with current configuration.
     *
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     */
    public function setReader(): void
    {
        $filePath = $this->getConfig()->getPath();

        if (! file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        if (! is_readable($filePath)) {
            throw new FileNotReadableException($filePath);
        }

        try {
            $this->reader = new SplFileObject($filePath);
        } catch (RuntimeException $e) {
            // SplFileObject throws RuntimeException for permission/access issues
            if (str_contains($e->getMessage(), 'Permission denied') || str_contains($e->getMessage(), 'Failed to open stream')) {
                throw new FileNotReadableException($filePath);
            }

            throw $e;
        }

        if ($this->reader->getSize() === 0) {
            throw new EmptyFileException($filePath);
        }

        $this->reader->setFlags(SplFileObject::READ_CSV);
        $this->reader->setCsvControl(
            $this->getConfig()->getDelimiter(),
            $this->getConfig()->getEnclosure(),
            $this->getConfig()->getEscape()
        );

        // Initialize position and cache headers like FastCSV extension does
        $this->initializeReader();
    }

    /**
     * Initialize reader state to match FastCSV extension behavior.
     *
     * Sets up initial position tracking and caches headers if needed.
     */
    private function initializeReader(): void
    {
        if (! $this->reader instanceof SplFileObject) {
            return;
        }

        $this->reader->rewind();
        $this->recordCount = null;
        $this->header = null;
        $this->position = -1;  // Start at -1 (no record read)

        if ($this->getConfig()->hasHeader()) {
            $this->cacheHeaders();
        }
    }

    /**
     * Cache headers from first line if hasHeader is true.
     *
     * Reads the first line of the CSV file and stores it as header data.
     */
    private function cacheHeaders(): void
    {
        if (! $this->getConfig()->hasHeader() || $this->header !== null) {
            return;
        }

        /** @var SplFileObject $reader */
        $reader = $this->getReader();
        if (! $reader instanceof SplFileObject) {
            return;
        }

        // Save current position
        $currentPosition = $reader->key();

        // Go to beginning and read header
        $reader->rewind();
        $headerRecord = $reader->current();

        if ($headerRecord !== false && $headerRecord !== [null] && is_array($headerRecord) && ! $this->isInvalidRecord($headerRecord)) {
            $this->header = $headerRecord;
        }

        // Restore position if it was valid
        if ($currentPosition >= 0) {
            $reader->seek($currentPosition);
        }
    }

    /**
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Total number of records, excluding header if configured
     */
    public function getRecordCount(): ?int
    {
        if ($this->recordCount === null) {
            /** @var SplFileObject $reader */
            $reader = $this->getReader();

            // Save current position
            $savedPosition = $reader->key();

            // Count actual valid records like FastCSV extension does
            $reader->rewind();

            // Skip header if configured
            if ($this->getConfig()->hasHeader()) {
                $reader->current(); // Read header
                $reader->next();    // Move past header
            }

            // Count remaining valid records
            $count = 0;
            while (! $reader->eof()) {
                $record = $reader->current();
                if ($record !== false && $record !== [null] && is_array($record) && ! $this->isInvalidRecord($record)) {
                    $count++;
                }
                $reader->next();
            }

            // Restore position
            if ($savedPosition >= 0) {
                $reader->seek($savedPosition);
            } else {
                $reader->rewind();
            }

            $this->recordCount = $count;
        }

        return $this->recordCount;
    }

    /**
     * Rewinds the reader to the beginning of the data records.
     *
     * Resets position to -1 (no record read state) and clears cached data.
     */
    public function rewind(): void
    {
        if (! $this->reader instanceof SplFileObject) {
            return;
        }

        $this->reader->rewind();
        parent::rewind();  // This will reset position and clear cache

        // Cache headers if needed
        if ($this->getConfig()->hasHeader() && $this->header === null) {
            $this->cacheHeaders();
        }
    }

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     */
    public function nextRecord(): array|false
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        // Calculate next file position
        $nextPosition = $this->position + 1;
        if ($nextPosition >= $this->getRecordCount()) {
            return false;
        }

        try {
            // Position the reader at the correct record
            if ($this->position === -1) {
                // First read - start from beginning
                if ($this->getConfig()->hasHeader()) {
                    // Cache header first
                    if ($this->header === null) {
                        $reader->rewind();
                        $headerRecord = $reader->current();
                        if ($headerRecord !== false && $headerRecord !== [null] && is_array($headerRecord) && ! $this->isInvalidRecord($headerRecord)) {
                            $this->header = $headerRecord;
                        }
                    }
                    // Now seek to first data record (line 1)
                    $reader->seek(1);
                } else {
                    // No header, start at beginning
                    $reader->rewind();
                }
                // Reader is now positioned at the first data record
            } else {
                // Subsequent reads - advance to next record
                $reader->next();
            }

            // Read records until we find a valid one
            do {
                $record = $reader->current();

                if ($record === false) {
                    return false;
                }

                // Ensure we have a valid array record
                if (! is_array($record)) {
                    $reader->next();
                    $nextPosition++;

                    continue;                   // skip non-array data
                }

                if ($this->isInvalidRecord($record)) {
                    $reader->next();
                    $nextPosition++;

                    continue;                   // skip invalid record
                }

                break;                          // valid record found
            } while (true);

            $this->position = $nextPosition;

            /** @var array $record */
            $this->cachedRecord = $record;

            /** @var array $record */
            return $record;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Gets the current 0-based record position.
     *
     * @return int Current position (-1 if no record has been read, 0+ for actual positions)
     */
    public function getCurrentPosition(): int
    {
        return $this->position;
    }

    /**
     * Gets the record at the current position without advancing.
     *
     * @return array|false Array of field values, or false if no record has been read
     */
    public function getRecord(): array|false
    {
        if ($this->position === -1) {
            return false;  // No record has been read yet
        }

        // Return cached record if available
        if ($this->cachedRecord !== null) {
            return $this->cachedRecord;
        }

        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        // Calculate file position
        $filePosition = $this->position;
        if ($this->getConfig()->hasHeader()) {
            $filePosition++; // Skip header line
        }

        try {
            $reader->seek($filePosition);
            $record = $reader->current();

            if ($record === false || $record === null || ! is_array($record)) {
                return false;
            }

            $this->cachedRecord = $record;  // Cache the record

            return $record;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Gets the header row if headers are enabled.
     *
     * @return array|false Array of header field names, or false if headers disabled
     */
    public function getHeader(): array|false
    {
        if (! $this->getConfig()->hasHeader()) {
            return false;
        }

        // Try to cache headers if not already cached
        if ($this->header === null) {
            $this->cacheHeaders();
        }

        return $this->header ?? false;
    }

    /**
     * Seeks to a specific record position (0-based)
     *
     * @param int $position The 0-based position to seek to
     * @return array|false The record at the specified position or false if invalid
     */
    public function seek(int $position): array|false
    {
        if ($position < 0 || $position >= $this->getRecordCount()) {
            return false;
        }

        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        // Calculate file line position
        $filePosition = $position;
        if ($this->getConfig()->hasHeader()) {
            $filePosition++; // Skip header line
        }

        try {
            $reader->seek($filePosition);
            $record = $reader->current();

            if ($record === false || $record === null || ! is_array($record)) {
                return false;
            }

            $this->position = $position;
            $this->cachedRecord = $record;  // Cache the record

            return $record;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Checks if the CSV file contains any data records.
     *
     * @return bool True if file contains records, false otherwise
     */
    public function hasRecords(): bool
    {
        return $this->getRecordCount() > 0;
    }

    /**
     * Checks if more records exist from the current position.
     *
     * @return bool True if more records available, false if at end
     */
    public function hasNext(): bool
    {
        return ($this->position + 1) < $this->getRecordCount();
    }

    /**
     * Sets the CSV file path and resets the reader.
     *
     * @param string $source Path to the CSV file
     */
    public function setSource(string $source): void
    {
        $this->getConfig()->setPath($source);

        if ($this->reader instanceof \SplFileObject) {
            $this->setReader();
        }
    }

    /**
     * Updates the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;

        $this->reset();

        if ($this->reader instanceof \SplFileObject) {
            $this->setReader();
        }
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     * @throws Exception If configuration is not set
     */
    public function getConfig(): CsvConfigInterface
    {
        if (! $this->config instanceof \CsvToolkit\Contracts\CsvConfigInterface) {
            throw new Exception("Configuration not set");
        }

        return $this->config;
    }

    /**
     * Gets the current CSV file path.
     *
     * @return string File path string
     */
    public function getSource(): string
    {
        return $this->getConfig()->getPath();
    }

    /**
     * Check if the record is considered invalid.
     *
     * @param array $record The record to validate
     * @return bool True if record is invalid
     */
    private function isInvalidRecord(array $record): bool
    {
        return count($record) === 1 && ($record[0] === null || $record[0] === '');
    }

    /**
     * Resets the reader state.
     *
     * Clears all cached data and resets position tracking.
     */
    private function reset(): void
    {
        $this->reader = null;
        $this->recordCount = null;
        $this->header = null;
        $this->position = -1;
    }
}
