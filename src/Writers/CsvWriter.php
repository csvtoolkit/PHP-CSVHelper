<?php

namespace Phpcsv\CsvHelper\Writers;

use Exception;
use FastCSVConfig;
use FastCSVWriter;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\CsvWriterException;
use Phpcsv\CsvHelper\Exceptions\DirectoryNotFoundException;
use SplFileObject;

/**
 * CSV Writer implementation using FastCSV extension
 *
 * This class provides high-performance CSV writing functionality using the FastCSV PHP extension.
 * It supports custom delimiters, enclosures, escape characters, and optional headers.
 */
class CsvWriter extends AbstractCsvWriter
{
    /**
     * FastCSV configuration object.
     */
    private ?FastCSVConfig $fastCsvConfig = null;

    /**
     * Creates a new FastCSV-based CSV writer instance.
     *
     * @param string|null $target Optional file path for output
     * @param CsvConfigInterface|null $config Optional configuration object
     * @param array|null $headers Optional header row
     */
    public function __construct(
        ?string $target = null,
        ?CsvConfigInterface $config = null,
        ?array $headers = null
    ) {
        $this->config = $config ?? new CsvConfig();
        $this->header = $headers;

        if ($target !== null) {
            $this->setTarget($target);
        }
    }

    /**
     * Gets the underlying FastCSVWriter instance.
     *
     * @return FastCSVWriter|SplFileObject|null The FastCSVWriter instance
     */
    public function getWriter(): SplFileObject|FastCSVWriter|null
    {
        if ($this->writer === null) {
            $this->setWriter();
        }

        return $this->writer;
    }

    /**
     * Initializes the FastCSVWriter with current configuration.
     *
     * @throws CsvWriterException If writer creation fails
     * @throws DirectoryNotFoundException If directory doesn't exist
     */
    public function setWriter(): void
    {
        $filePath = $this->getConfig()->getPath();

        if (in_array(trim($filePath), ['', '0'], true)) {
            throw new CsvWriterException('Target file path is required');
        }

        // Check if directory exists
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            throw new DirectoryNotFoundException($directory);
        }

        $this->fastCsvConfig = new FastCSVConfig();
        $this->fastCsvConfig
            ->setPath($filePath)
            ->setDelimiter($this->config->getDelimiter())
            ->setEnclosure($this->config->getEnclosure())
            ->setEscape($this->config->getEscape());

        try {
            $this->writer = new FastCSVWriter($this->fastCsvConfig, $this->header ?? []);
        } catch (Exception $e) {
            throw new CsvWriterException("Failed to initialize FastCSV writer: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    /**
     * Writes a single record to the CSV file.
     *
     * @param array $data Array of field values to write
     * @throws CsvWriterException If writing fails
     */
    public function write(array $data): void
    {
        /** @var FastCSVWriter $writer */
        $writer = $this->getWriter();

        if (! $writer->writeRecord($data)) {
            throw new CsvWriterException('Failed to write CSV record');
        }
    }

    /**
     * Writes a record using an associative array mapped to headers.
     *
     * @param array $fieldsMap Associative array mapping header names to values
     * @throws CsvWriterException If writing fails
     */
    public function writeMap(array $fieldsMap): void
    {
        /** @var FastCSVWriter $writer */
        $writer = $this->getWriter();

        if (! $writer->writeRecordMap($fieldsMap)) {
            throw new CsvWriterException('Failed to write CSV record map');
        }
    }

    /**
     * Writes multiple records to the CSV file.
     *
     * @param array<int, array> $records Array of records to write
     * @throws CsvWriterException If writing fails
     */
    public function writeAll(array $records): void
    {
        foreach ($records as $record) {
            if (! is_array($record)) {
                throw new \InvalidArgumentException('Each record must be an array');
            }
            $this->write($record);
        }
    }

    /**
     * Sets the CSV headers.
     *
     * @param array $headers Array of header strings
     */
    public function setHeaders(array $headers): void
    {
        $this->header = $headers;

        // If writer is already initialized, we need to recreate it with new headers
        if ($this->writer !== null) {
            $this->close();
            $this->writer = null;
        }
    }

    /**
     * Gets the CSV headers.
     *
     * @return array|null Array of header strings or null if not set
     */
    public function getHeaders(): ?array
    {
        return $this->header;
    }

    /**
     * Closes the writer and frees resources.
     */
    public function close(): void
    {
        if ($this->writer instanceof FastCSVWriter) {
            $this->writer->close();
        }
    }

    /**
     * Sets the output file path.
     *
     * @param string $target File path for CSV output
     */
    public function setTarget(string $target): void
    {
        $this->config->setPath($target);

        // Reset writer if target changes
        if ($this->writer !== null) {
            $this->close();
            $this->writer = null;
            $this->fastCsvConfig = null;
        }
    }

    /**
     * Gets the current output file path.
     *
     * @return string File path string
     */
    public function getTarget(): string
    {
        return $this->config->getPath();
    }

    /**
     * Gets the source file path (alias for getTarget).
     *
     * @return string File path string
     */
    public function getSource(): string
    {
        return $this->getTarget();
    }

    /**
     * Sets the source file path (alias for setTarget).
     *
     * @param string $source File path to set
     */
    public function setSource(string $source): void
    {
        $this->setTarget($source);
    }

    /**
     * Sets the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;

        $this->reset();
    }

    /**
     * Resets the writer state.
     *
     * Closes the current writer and clears all cached data.
     */
    public function reset(): void
    {
        if ($this->writer instanceof FastCSVWriter) {
            $this->writer->close();
        }
        $this->writer = null;
        $this->fastCsvConfig = null;
    }

    /**
     * Destructor to clean up FastCSV resources.
     */
    public function __destruct()
    {
        $this->close();
    }
}
