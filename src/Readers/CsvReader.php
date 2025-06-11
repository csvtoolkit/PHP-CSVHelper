<?php

namespace CsvToolkit\Readers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Exceptions\CsvReaderException;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;
use Exception;
use FastCSVConfig;
use FastCSVReader;
use SplFileObject;

/**
 * CSV Reader implementation using FastCSV extension
 *
 * This class provides high-performance functionality to read CSV files using the FastCSV C extension.
 * It supports custom delimiters, enclosures, and escape characters
 */
class CsvReader extends AbstractCsvReader
{
    /**
     * FastCSV configuration object.
     */
    private ?FastCSVConfig $fastCsvConfig = null;

    /**
     * Creates a new FastCSV-based CSV reader instance.
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
     * Gets the underlying FastCSVReader instance.
     *
     * @return FastCSVReader|SplFileObject|null The FastCSVReader instance
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     * @throws CsvReaderException If FastCSV reader creation fails
     */
    public function getReader(): null|SplFileObject|FastCSVReader
    {
        if (! $this->reader instanceof FastCSVReader) {
            $this->setReader();
        }

        return $this->reader;
    }

    /**
     * Initializes the FastCSVReader with current configuration.
     *
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     * @throws CsvReaderException If FastCSV reader creation fails
     */
    public function setReader(): void
    {
        if (! $this->config instanceof \CsvToolkit\Contracts\CsvConfigInterface) {
            throw new Exception("Configuration is required");
        }

        $this->fastCsvConfig = new FastCSVConfig();
        $this->fastCsvConfig
            ->setPath($this->config->getPath())
            ->setDelimiter($this->config->getDelimiter())
            ->setEnclosure($this->config->getEnclosure())
            ->setEscape($this->config->getEscape())
            ->setHasHeader($this->config->hasHeader())
            ->setOffset($this->config->getOffset());

        // Check if file is empty before creating reader
        $filePath = $this->config->getPath();
        if (file_exists($filePath) && filesize($filePath) === 0) {
            throw new EmptyFileException("File is empty: " . $filePath);
        }

        try {
            $this->reader = new FastCSVReader($this->fastCsvConfig);
        } catch (Exception $e) {
            $message = $e->getMessage();

            // Check for specific error types to throw appropriate exceptions
            if (str_contains($message, 'No such file or directory') ||
                str_contains($message, 'Failed to open CSV file')) {
                throw new FileNotFoundException("File not found: " . $this->config->getPath(), 0, $e);
            }

            if (str_contains($message, 'Permission denied')) {
                throw new FileNotReadableException("File not readable: " . $this->config->getPath());
            }

            if (str_contains($message, 'empty') || str_contains($message, 'no records')) {
                throw new EmptyFileException("File is empty: " . $this->config->getPath());
            }

            throw new CsvReaderException("Failed to create FastCSV reader: " . $message, $e->getCode(), $e);
        }

        if ($this->config->hasHeader()) {
            $this->cacheHeaders();
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
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Total number of records, excluding header if configured
     */
    public function getRecordCount(): ?int
    {
        if ($this->recordCount !== null) {
            return $this->recordCount;
        }

        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        $this->recordCount = $reader->getRecordCount();

        return $this->recordCount;
    }

    /**
     * Rewinds the reader to the beginning of the data records.
     *
     * Resets position to -1 (no record read state) and clears cached data.
     */
    public function rewind(): void
    {
        if (! $this->reader instanceof FastCSVReader) {
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

        // If not cached, seek to current position to get the record
        return $this->seek($this->position);
    }

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     */
    public function nextRecord(): array|false
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        // Calculate next file position
        $nextPosition = $this->position + 1;
        if ($nextPosition >= $this->getRecordCount()) {
            return false;
        }

        try {
            $record = $reader->nextRecord();

            if ($record === false || $record === null) {
                return false;
            }

            // Update position only after successful read
            $this->position = $nextPosition;
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

        if ($this->header !== null) {
            return $this->header;
        }

        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        $headers = $reader->getHeaders();
        if ($headers !== false) {
            $this->header = $headers;
        }

        return $headers;
    }

    /**
     * Seeks to a specific 0-based record position.
     *
     * @param int $position Zero-based position to seek to
     * @return array|false Array of field values at the position, or false if invalid position
     */
    public function seek(int $position): array|false
    {
        if ($position < 0 || $position >= $this->getRecordCount()) {
            return false;
        }

        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        try {
            // FastCSV seek now returns the record data directly
            $record = $reader->seek($position);

            if ($record === false || $record === null) {
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
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        $currentPosition = $reader->getPosition();

        // If we're past position 0, records definitely exist
        if ($currentPosition > 0) {
            return true;
        }

        // If we're at position 0 or before, check if there's a next record
        return $reader->hasNext();
    }

    /**
     * Checks if more records exist from the current position.
     *
     * @return bool True if more records available, false if at end
     */
    public function hasNext(): bool
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        return $reader->hasNext();
    }

    /**
     * Sets the CSV file path and resets the reader.
     *
     * @param string $source Path to the CSV file
     */
    public function setSource(string $source): void
    {
        $this->getConfig()->setPath($source);

        $this->reader = null;
        $this->fastCsvConfig = null;
        $this->recordCount = null;
        $this->header = null;
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
     * Updates the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;

        if ($this->reader instanceof FastCSVReader && $this->fastCsvConfig instanceof FastCSVConfig) {
            $this->fastCsvConfig
                ->setPath($config->getPath())
                ->setDelimiter($config->getDelimiter())
                ->setEnclosure($config->getEnclosure())
                ->setEscape($config->getEscape())
                ->setHasHeader($config->hasHeader())
                ->setOffset($config->getOffset());

            if ($this->reader->setConfig($this->fastCsvConfig)) {
                $this->recordCount = null;
                $this->header = null;

                return;
            }
        }

        $this->reset();
        $this->setReader();
    }

    /**
     * Destructor to clean up FastCSV resources.
     */
    public function __destruct()
    {
        if ($this->reader instanceof FastCSVReader) {
            $this->reader->close();
        }
    }

    /**
     * Resets the reader state.
     *
     * Clears all cached data and resets position tracking.
     */
    public function reset(): void
    {
        $this->reader = null;
        $this->fastCsvConfig = null;
        $this->recordCount = null;
        $this->header = null;
    }

    /**
     * Cache headers from the CSV file.
     *
     * Retrieves and stores header data for subsequent access.
     */
    private function cacheHeaders(): void
    {
        $headers = $this->getHeader();
        if ($headers !== false) {
            $this->header = $headers;
        }
    }
}
