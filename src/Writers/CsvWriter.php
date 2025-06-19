<?php

namespace CsvToolkit\Writers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Exceptions\CsvWriterException;
use CsvToolkit\Exceptions\DirectoryNotFoundException;
use CsvToolkit\Helpers\ExtensionHelper;
use CsvToolkit\Helpers\FileValidator;
use Exception;
use FastCSVWriter;
use SplFileObject;

/**
 * CSV Writer implementation using FastCSV extension
 *
 * This class provides high-performance CSV writing functionality using the FastCSV PHP extension.
 * It supports custom delimiters, enclosures, escape characters, encoding, and all extended features.
 */
class CsvWriter implements CsvWriterInterface
{
    protected CsvConfig $config;

    protected ?FastCSVWriter $writer = null;

    protected bool $headerWritten = false;

    protected ?array $header = null;

    private ?\FastCSVConfig $fastCsvConfig = null;

    /**
     * Creates a new FastCSV-based CSV writer instance.
     *
     * @param string|null $destination Optional file path to write CSV to
     * @param CsvConfig|null $config Optional configuration object
     * @throws \RuntimeException If FastCSV extension is not available
     */
    public function __construct(
        ?string $destination = null,
        ?CsvConfig $config = null
    ) {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            throw new \RuntimeException('FastCSV extension is not available');
        }

        $this->config = $config ?? new CsvConfig();

        if ($destination !== null) {
            $this->setDestination($destination);
        }
    }

    /**
     * Gets the underlying FastCSVWriter instance.
     *
     * @return FastCSVWriter|SplFileObject|null The FastCSVWriter instance
     */
    public function getWriter(): SplFileObject|FastCSVWriter|null
    {
        if (! $this->writer instanceof \FastCSVWriter) {
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
        FileValidator::validateFileWritable($filePath);

        try {
            $this->fastCsvConfig = $this->config->toFastCsvConfig();
            $this->writer = new FastCSVWriter($this->fastCsvConfig, $this->header ?? []);
        } catch (Exception $e) {
            throw new CsvWriterException("Failed to initialize FastCSV writer: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfig The configuration object
     */
    public function getConfig(): CsvConfig
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
        if ($this->writer instanceof \FastCSVWriter) {
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
     * Manually flushes buffered data to disk.
     * This method is useful when auto-flush is disabled for performance reasons.
     * Call this periodically (e.g., every 1000 records) to ensure data is written.
     *
     * @return bool True on success, false on failure
     * @throws CsvWriterException If writer is not initialized or flush operation fails
     */
    public function flush(): bool
    {
        if (! $this->writer instanceof FastCSVWriter) {
            throw new CsvWriterException('Writer is not initialized');
        }

        $result = $this->writer->flush();
        if (! $result) {
            throw new CsvWriterException('Failed to flush data to file');
        }

        return $result;
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
     * @param string $destination File path for CSV output
     */
    public function setDestination(string $destination): void
    {
        $this->config->setPath($destination);

        // Reset writer if destination changes
        if ($this->writer instanceof \FastCSVWriter) {
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
    public function getDestination(): string
    {
        return $this->config->getPath();
    }

    /**
     * Gets the source file path (alias for getDestination).
     *
     * @return string File path string
     */
    public function getSource(): string
    {
        return $this->getDestination();
    }

    /**
     * Sets the source file path (alias for setDestination).
     *
     * @param string $source File path to set
     */
    public function setSource(string $source): void
    {
        $this->setDestination($source);
    }

    /**
     * Sets the output file path (alias for setDestination).
     *
     * @param string $target File path for CSV output
     */
    public function setTarget(string $target): void
    {
        $this->setDestination($target);
    }

    /**
     * Gets the current output file path (alias for getDestination).
     *
     * @return string File path string
     */
    public function getTarget(): string
    {
        return $this->getDestination();
    }

    /**
     * Updates the CSV configuration and resets the writer.
     *
     * @param CsvConfig|SplConfig $config New configuration
     */
    public function setConfig(CsvConfig|SplConfig $config): void
    {
        if ($config instanceof SplConfig) {
            // Convert SplConfig to CsvConfig
            $csvConfig = new CsvConfig();
            $csvConfig->setPath($config->getPath())
                      ->setDelimiter($config->getDelimiter())
                      ->setEnclosure($config->getEnclosure())
                      ->setEscape($config->getEscape())
                      ->setHasHeader($config->hasHeader());
            $this->config = $csvConfig;
        } else {
            $this->config = $config;
        }

        if ($this->writer instanceof \FastCSVWriter) {
            $this->close();
            $this->writer = null;
        }
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
