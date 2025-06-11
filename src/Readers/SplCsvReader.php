<?php

namespace Phpcsv\CsvHelper\Readers;

use FastCSVReader;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\EmptyFileException;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Exceptions\FileNotReadableException;
use RuntimeException;
use SplFileObject;

/**
 * CSV Reader implementation using SplFileObject
 *
 * This class provides functionality to read CSV files using PHP's built-in SplFileObject.
 * It supports custom delimiters, enclosures, and escape characters.
 */
class SplCsvReader extends AbstractCsvReader
{
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
     */
    public function getReader(): null|SplFileObject|FastCSVReader
    {
        if (! $this->reader instanceof SplFileObject) {
            $this->setReader();
        }

        return $this->reader;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws EmptyFileException
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

        $this->position = 0;
        $this->recordCount = null;
        $this->header = null;
    }

    /**
     * @return int|null Total number of records, excluding header if configured
     */
    public function getRecordCount(): ?int
    {
        if ($this->recordCount === null) {
            $currentPosition = $this->getCurrentPosition();
            /** @var SplFileObject $reader */
            $reader = $this->getReader();
            $this->rewind();
            $reader->seek(PHP_INT_MAX);
            $this->recordCount = $reader->key();
            $reader->seek($currentPosition);
            if ($this->config->hasHeader()) {
                $this->recordCount--;
            }
        }

        return $this->recordCount;
    }

    public function rewind(): void
    {
        if (! $this->reader instanceof SplFileObject) {
            return;
        }
        $this->position = 0;
        $this->reader->rewind();
    }

    /**
     * @return array|false Array containing CSV fields or false on EOF/error
     */
    public function getRecord(): array|false
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        $reader->seek($this->getCurrentPosition());
        $record = $reader->current();

        $this->position++;

        if ($record === false) {
            return false;
        }

        if (is_string($record)) {
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

        $currentPosition = $this->getCurrentPosition();

        $this->rewind();
        $record = $this->getRecord();
        if ($record === false) {
            return false;
        }
        /** @var SplFileObject $reader */
        $reader = $this->getReader();
        $reader->seek($currentPosition);
        $this->position = $currentPosition;
        $this->header = $record;

        return $record;
    }

    /**
     * @param  int  $position  Zero-based record position
     * @return array|false Record at position or false on error
     */
    public function seek(int $position): array|false
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();
        $this->position = $position;
        $reader->seek($position);

        $record = $reader->current();

        if ($record === false) {
            return false;
        }

        if (is_string($record)) {
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
        $currentPosition = $this->getCurrentPosition();

        // If we're past position 0, records definitely exist
        if ($currentPosition > 0) {
            return true;
        }

        // If we're at position 0, check if there's a next record
        return $this->hasNext();
    }

    /**
     * @return bool True if more records exist from current position (EOF check)
     */
    public function hasNext(): bool
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        return ! $reader->eof();
    }

    /**
     * @param  string  $source  File path
     */
    public function setSource(string $source): void
    {
        $this->config->setPath($source);

        if ($this->reader instanceof \SplFileObject) {
            $this->setReader();
        }
    }

    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;

        $this->reset();

        if ($this->reader instanceof \SplFileObject) {
            $this->setReader();
        }
    }

    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    public function getCurrentPosition(): int
    {
        return $this->position;
    }

    public function getSource(): string
    {
        return $this->config->getPath();
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

    private function reset(): void
    {
        $this->reader = null;
        $this->recordCount = null;
        $this->header = null;
    }
}
