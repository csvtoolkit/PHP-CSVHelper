<?php

namespace CsvToolkit\Readers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Exceptions\CsvReaderException;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;
use CsvToolkit\Helpers\FileValidator;
use Exception;
use FastCSVReader;

/**
 * CSV Reader implementation using FastCSV extension
 *
 * This class provides high-performance functionality to read CSV files using the FastCSV C extension.
 * It supports custom delimiters, enclosures, escape characters, and all extended features.
 */
class CsvReader implements CsvReaderInterface
{
    protected ?CsvConfig $config = null;

    protected array|false|null $header = null;

    protected ?int $recordCount = null;

    protected int $position = -1;

    protected array|false|null $cachedRecord = null;

    protected ?FastCSVReader $reader = null;

    private ?\FastCSVConfig $fastCsvConfig = null;

    /**
     * Creates a new FastCSV-based CSV reader instance.
     *
     * @param  string|null  $source  Optional file path to CSV file
     * @param  CsvConfig|null  $config  Optional configuration object
     * @throws Exception
     */
    public function __construct(
        ?string $source = null,
        ?CsvConfig $config = null
    ) {
        $this->config = $config ?? new CsvConfig();

        if ($source !== null) {
            $this->setSource($source);
        }
    }

    /**
     * Gets the underlying FastCSVReader instance.
     *
     * @return FastCSVReader The FastCSVReader instance
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     * @throws CsvReaderException|Exception If FastCSV reader creation fails
     */
    public function getReader(): FastCSVReader
    {
        if (! $this->reader instanceof FastCSVReader) {
            $this->setReader();
        }

        assert($this->reader instanceof FastCSVReader);

        return $this->reader;
    }

    /**
     * Initializes the FastCSVReader with current configuration.
     *
     * @throws FileNotFoundException If the CSV file doesn't exist
     * @throws FileNotReadableException If the file cannot be read
     * @throws EmptyFileException If the file is empty
     * @throws CsvReaderException|Exception If FastCSV reader creation fails
     */
    public function setReader(): void
    {
        if (! $this->config instanceof CsvConfig) {
            throw new Exception("Configuration is required");
        }

        $filePath = $this->config->getPath();
        FileValidator::validateFileReadable($filePath);

        $this->fastCsvConfig = $this->config->toFastCsvConfig();

        try {
            $this->reader = new FastCSVReader($this->fastCsvConfig);
        } catch (Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'No such file or directory') ||
                str_contains($message, 'Failed to open CSV file')) {
                throw new FileNotFoundException($this->config->getPath(), 0, $e);
            }

            if (str_contains($message, 'Permission denied')) {
                throw new FileNotReadableException($this->config->getPath());
            }

            if (str_contains($message, 'empty') || str_contains($message, 'no records')) {
                throw new EmptyFileException($this->config->getPath());
            }

            throw new CsvReaderException("Failed to create FastCSV reader: " . $message, $e->getCode(), $e);
        }
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfig The configuration object
     * @throws Exception If configuration is not set
     */
    public function getConfig(): CsvConfig
    {
        if (! $this->config instanceof CsvConfig) {
            throw new Exception("Configuration not set");
        }

        return $this->config;
    }

    /**
     * Gets the total number of data records in the CSV file.
     *
     * @return int|null Total number of records, excluding header if configured
     * @throws Exception
     */
    public function getRecordCount(): ?int
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();
        if ($this->recordCount !== null) {
            return $this->recordCount;
        }

        $this->recordCount = $reader->getRecordCount();

        return $this->recordCount;
    }

    /**
     * Rewinds the reader to the beginning of the data records.
     *
     * Resets position to -1 (no record read state) and clears cached data.
     * @throws Exception
     */
    public function rewind(): void
    {

        $this->getReader()->rewind();
        $this->position = -1;
        $this->cachedRecord = null;

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
        if ($this->position < 0) {
            return false;
        }

        if ($this->cachedRecord !== null && $this->cachedRecord !== false) {
            return $this->cachedRecord;
        }

        $this->cachedRecord = $this->seek($this->position);

        return $this->cachedRecord;
    }

    /**
     * Reads the next record sequentially.
     *
     * @return array|false Array of field values, or false if end of file
     * @throws Exception
     */
    public function nextRecord(): array|false
    {
        if (! $this->hasNext()) {
            return false;
        }

        $record = $this->getReader()->nextRecord();

        $this->cachedRecord = $record ?? false;
        $this->position++;

        return $this->cachedRecord;
    }

    /**
     * Gets the header row if headers are enabled.
     *
     * @return array|false Array of header field names, or false if headers disabled
     * @throws Exception
     */
    public function getHeader(): array|false
    {
        if (! $this->getConfig()->hasHeader()) {
            return false;
        }

        if ($this->header !== null && $this->header !== false) {
            return $this->header;
        }

        $this->header = $this->getReader()->getHeaders();

        return $this->header;
    }

    /**
     * Seeks to a specific 0-based record position.
     *
     * @param int $position Zero-based position to seek to
     * @return array|false Array of field values at the position, or false if invalid position
     */
    public function seek(int $position): array|false
    {
        if ($position < 0) {
            return false;
        }

        try {
            $reader = $this->getReader();
            $seekResult = $reader->seek($position);

            if ($seekResult === false) {
                return false;
            }

            // After successful seek, get the current record
            $record = $reader->nextRecord();

            $this->position = $position;
            $this->cachedRecord = $record ?? false;

            return $this->cachedRecord;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Checks if the CSV file contains any data records.
     *
     * @return bool True if file contains records, false otherwise
     * @throws Exception
     */
    public function hasRecords(): bool
    {
        if ($this->position > 0) {
            return true;
        }

        return $this->getReader()->hasNext();
    }

    /**
     * Checks if more records exist from the current position.
     *
     * @return bool True if more records available, false if at end
     * @throws Exception
     */
    public function hasNext(): bool
    {
        return $this->getReader()->hasNext();
    }

    /**
     * Sets the CSV file path and resets the reader.
     *
     * @param  string  $source  Path to the CSV file
     * @throws Exception
     */
    public function setSource(string $source): void
    {
        $this->getConfig()->setPath($source);

        $this->reader = null;
        $this->recordCount = null;
        $this->header = null;
    }

    /**
     * Gets the current CSV file path.
     *
     * @return string File path string
     * @throws Exception
     */
    public function getSource(): string
    {
        return $this->getConfig()->getPath();
    }

    public function setConfig(CsvConfig $config): self
    {
        $this->config = $config;

        return $this;
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
}
