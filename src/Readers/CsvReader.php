<?php

namespace Phpcsv\CsvHelper\Readers;

use Exception;
use FastCSVConfig;
use FastCSVReader;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\CsvReaderException;
use Phpcsv\CsvHelper\Exceptions\EmptyFileException;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Exceptions\FileNotReadableException;
use SplFileObject;

/**
 * CSV Reader implementation using FastCSV extension
 *
 * This class provides high-performance functionality to read CSV files using the FastCSV C extension.
 * It supports custom delimiters, enclosures, and escape characters with native C performance.
 */
class CsvReader extends AbstractCsvReader
{
    private ?FastCSVConfig $fastCsvConfig = null;

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
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws EmptyFileException
     * @throws CsvReaderException
     */
    public function getReader(): null|SplFileObject|FastCSVReader
    {
        if (! $this->reader instanceof FastCSVReader) {
            $this->setReader();
        }

        return $this->reader;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws EmptyFileException
     * @throws CsvReaderException
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

        $this->fastCsvConfig = new FastCSVConfig();
        $this->fastCsvConfig
            ->setPath($filePath)
            ->setDelimiter($this->config->getDelimiter())
            ->setEnclosure($this->config->getEnclosure())
            ->setEscape($this->config->getEscape())
            ->setHasHeader($this->config->hasHeader())
            ->setOffset($this->config->getOffset());

        try {
            $this->reader = new FastCSVReader($this->fastCsvConfig);
        } catch (Exception $e) {
            throw new CsvReaderException("Failed to initialize FastCSV reader: " . $e->getMessage());
        }

        if ($this->reader->getRecordCount() === 0 && ! $this->config->hasHeader()) {
            throw new EmptyFileException($filePath);
        }

        $this->recordCount = null;
        $this->header = null;
    }

    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    /**
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

    public function rewind(): void
    {
        if (! $this->reader instanceof FastCSVReader) {
            return;
        }
        $this->reader->rewind();
    }

    public function getCurrentPosition(): int
    {
        if ($this->reader instanceof FastCSVReader) {
            return $this->reader->getPosition();
        }

        return 0;
    }

    /**
     * @return array|false Array containing CSV fields or false on EOF/error
     */
    public function getRecord(): array|false
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();


        $record = $reader->nextRecord();
        if ($record === false) {
            return false;
        }
        if ($this->isInvalidRecord($record)) {
            return false;
        }

        return $record;
    }

    /**
     * @return array|false Header row or false if headers disabled/error
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
     * @param  int  $position  Zero-based record position
     * @return array|false Record at position or false on error
     */
    public function seek(int $position): array|false
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        if (! $reader->seek($position)) {
            return false;
        }

        $record = $reader->nextRecord();
        if ($record === false) {
            return false;
        }
        if ($this->isInvalidRecord($record)) {
            return false;
        }

        return $record;
    }

    /**
     * @return bool True if file contains any records
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
     * @return bool True if more records exist from current position (EOF check)
     */
    public function hasNext(): bool
    {
        /** @var FastCSVReader $reader */
        $reader = $this->getReader();

        return $reader->hasNext();
    }

    /**
     * @param  string  $source  File path
     */
    public function setSource(string $source): void
    {
        $this->config->setPath($source);

        $this->reader = null;
        $this->fastCsvConfig = null;
        $this->recordCount = null;
        $this->header = null;
    }

    public function getSource(): string
    {
        return $this->config->getPath();
    }

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
     * Check if the record is considered invalid
     *
     * @param  array  $record  The record to validate
     * @return bool True if record is invalid
     */
    private function isInvalidRecord(array $record): bool
    {
        return count($record) === 1 && ($record[0] === null || $record[0] === '');
    }

    public function __destruct()
    {
        if ($this->reader instanceof FastCSVReader) {
            $this->reader->close();
        }
    }

    public function reset(): void
    {
        $this->reader = null;
        $this->fastCsvConfig = null;
        $this->recordCount = null;
        $this->header = null;
    }
}
