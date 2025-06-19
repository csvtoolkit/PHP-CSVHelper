<?php

namespace CsvToolkit\Readers;

use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;
use CsvToolkit\Helpers\FileValidator;
use Exception;
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
class SplCsvReader implements CsvReaderInterface
{
    protected ?SplConfig $config = null;

    protected ?array $header = null;

    protected ?int $recordCount = null;

    protected int $position = -1;

    protected ?array $cachedRecord = null;

    protected ?SplFileObject $reader = null;

    /**
     * Creates a new SplFileObject-based CSV reader instance.
     *
     * @param string|null $source Optional file path to CSV file
     * @param SplConfig|null $config Optional configuration object
     */
    public function __construct(
        ?string $source = null,
        ?SplConfig $config = null
    ) {
        $this->config = $config ?? new SplConfig();

        if ($source !== null) {
            $this->setSource($source);
        }
    }

    /**
     * Gets the underlying SplFileObject instance.
     *
     * @return SplFileObject The SplFileObject instance
     */
    public function getReader(): SplFileObject
    {
        if (! $this->reader instanceof SplFileObject) {
            $this->setReader();
        }

        assert($this->reader instanceof SplFileObject);

        return $this->reader;
    }

    /**
     * Initializes the SplFileObject with current configuration.
     *
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException|Exception If the file is empty
     */
    public function setReader(): void
    {
        $filePath = $this->getConfig()->getPath();
        FileValidator::validateFileReadable($filePath);

        try {
            $this->reader = new SplFileObject($filePath);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Permission denied') || str_contains($e->getMessage(), 'Failed to open stream')) {
                throw new FileNotReadableException($filePath);
            }

            throw $e;
        }

        $this->reader->setFlags(SplFileObject::READ_CSV);
        $this->reader->setCsvControl(
            $this->getConfig()->getDelimiter(),
            $this->getConfig()->getEnclosure(),
            $this->getConfig()->getEscape()
        );
    }

    /**
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Total number of records, excluding header if configured
     */
    public function getRecordCount(): ?int
    {
        if ($this->recordCount !== null) {
            return $this->recordCount;
        }

        $reader = $this->getReader();

        try {
            $currentPosition = $reader->ftell();

            // Handle ftell() returning false
            if ($currentPosition === false) {
                $currentPosition = 0;
            }

            // Count actual lines by iterating
            $reader->rewind();
            $lineCount = 0;
            while ($reader->valid()) {
                $line = $reader->current();
                // Only count non-empty lines or lines with actual content
                if ($line !== false && $line !== null && $line !== [] && $line !== [null]) {
                    $lineCount++;
                }
                $reader->next();
            }

            $reader->fseek($currentPosition);

            $this->recordCount = $this->getConfig()->hasHeader() ? max(0, $lineCount - 1) : $lineCount;

            return $this->recordCount;

        } catch (Exception) {
            return null;
        }
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
        $this->position = -1;
        $this->cachedRecord = null;
        // Don't clear header cache as it's still valid
    }

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     * @throws Exception
     */
    public function nextRecord(): array|false
    {
        $reader = $this->getReader();

        try {
            if ($this->position === -1) {
                // First read - ensure we start from the correct position
                $reader->rewind();

                if ($this->getConfig()->hasHeader()) {
                    // Cache header if not already cached
                    if ($this->header === null) {
                        $headerRecord = $reader->current();
                        if ($headerRecord !== false && $headerRecord !== [null] && is_array($headerRecord) && ! $this->isInvalidRecord($headerRecord)) {
                            $this->header = $headerRecord;
                        }
                    }
                    // Always move to first data record, regardless of current position
                    $reader->seek(1); // Seek to line 1 (first data record)
                } else {
                    // No header, start from line 0
                    $reader->seek(0);
                }

                $this->position = 0;
            } else {
                // Subsequent reads - advance to next record
                $reader->next();
                $this->position++;
            }

            // Read records until we find a valid one or reach EOF
            while ($reader->valid()) {
                $record = $reader->current();

                if ($record === false || ! is_array($record)) {
                    $reader->next();
                    $this->position++;

                    continue;
                }

                if ($this->isInvalidRecord($record)) {
                    $reader->next();
                    $this->position++;

                    continue;
                }

                // Valid record found
                $this->cachedRecord = $record;

                return $record;
            }

            // End of file reached
            return false;

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
    public function getRecord(?int $position = null): array|false
    {
        if ($position !== null) {
            return $this->seek($position);
        }

        if ($this->position === -1) {
            return false;  // No record has been read yet
        }

        // Return cached record if available
        if ($this->cachedRecord !== null) {
            return $this->cachedRecord;
        }

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

        // Return cached header if available
        if ($this->header !== null) {
            return $this->header;
        }

        // Read header from file and restore position
        $reader = $this->getReader();

        try {
            // Save current position more carefully
            $currentPosition = $reader->ftell();
            $currentKey = $reader->key(); // SplFileObject line number

            // Go to beginning and read header
            $reader->rewind();
            $headerRecord = $reader->current();

            if ($headerRecord !== false && is_array($headerRecord) && ! $this->isInvalidRecord($headerRecord)) {
                $this->header = $headerRecord;
            }

            // Restore position more accurately
            if ($currentPosition !== false && $currentKey !== null) {
                // If we had a valid position, seek back to that line
                $reader->seek($currentKey);
            } else {
                // If no valid position, rewind to start
                $reader->rewind();
            }

            return $this->header ?? false;

        } catch (Exception) {
            return false;
        }
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
     * @param SplConfig $config New configuration
     */
    public function setConfig(SplConfig $config): void
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
     * @return SplConfig The configuration object
     * @throws Exception If configuration is not set
     */
    public function getConfig(): SplConfig
    {
        if (! $this->config instanceof SplConfig) {
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
