<?php

namespace Phpcsv\CsvHelper\Writers;

use Exception;
use FastCSVConfig;
use FastCSVWriter;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\CsvWriterException;
use SplFileObject;

/**
 * CSV Writer implementation using FastCSV extension
 *
 * This class provides high-performance CSV writing functionality using the FastCSV PHP extension.
 * It supports custom delimiters, enclosures, escape characters, and optional headers.
 */
class CsvWriter extends AbstractCsvWriter
{
    private ?FastCSVConfig $fastCsvConfig = null;

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

    public function getWriter(): SplFileObject|FastCSVWriter|null
    {
        if ($this->writer === null) {
            $this->setWriter();
        }

        return $this->writer;
    }

    /**
     * @throws CsvWriterException
     */
    public function setWriter(): void
    {
        $filePath = $this->getConfig()->getPath();

        if ($filePath === '' || $filePath === '0') {
            throw new CsvWriterException('Target file path is required');
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
            throw new CsvWriterException("Failed to initialize FastCSV writer: " . $e->getMessage());
        }
    }

    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    /**
     * Writes a single record to the CSV file
     *
     * @throws CsvWriterException
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
     * Writes a record using an associative array mapped to headers
     *
     * @param array $fieldsMap Associative array mapping header names to values
     * @throws CsvWriterException
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
     * Writes multiple records to the CSV file
     *
     * @param array<array> $records Array of records to write
     * @throws CsvWriterException
     */
    public function writeAll(array $records): void
    {
        foreach ($records as $record) {
            $this->write($record);
        }
    }

    /**
     * Sets the CSV headers
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
     * Gets the CSV headers
     *
     * @return array|null Array of header strings or null if not set
     */
    public function getHeaders(): ?array
    {
        return $this->header;
    }

    /**
     * Closes the writer and frees resources
     */
    public function close(): void
    {
        if ($this->writer instanceof FastCSVWriter) {
            $this->writer->close();
        }
    }

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

    public function getTarget(): string
    {
        return $this->config->getPath();
    }

    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;

        if ($this->writer instanceof FastCSVWriter && $this->fastCsvConfig instanceof FastCSVConfig) {
            $this->fastCsvConfig
                ->setPath($config->getPath())
                ->setDelimiter($config->getDelimiter())
                ->setEnclosure($config->getEnclosure())
                ->setEscape($config->getEscape());
        }

        $this->reset();
    }

    /**
     * Resets the writer state
     */
    public function reset(): void
    {
        if ($this->writer instanceof FastCSVWriter) {
            $this->writer->close();
        }
        $this->writer = null;
        $this->fastCsvConfig = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
