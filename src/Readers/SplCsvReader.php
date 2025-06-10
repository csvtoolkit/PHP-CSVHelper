<?php

namespace Phpcsv\CsvHelper\Readers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\EmptyFileException;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Exceptions\FileNotReadableException;
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
    public function getReader(): ?SplFileObject
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

        $this->reader = new SplFileObject($filePath);

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
        $this->position = $position - 1;
        $reader->seek($position);

        return $this->getRecord();
    }

    /**
     * @return bool True if more records exist
     */
    public function hasRecords(): bool
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

        if ($this->reader instanceof \SplFileObject) {
            $this->setReader();
        }
    }

    /**
     * @see CsvConfigInterface::hasHeader()
     */
    public function skipHeader(): void
    {
        if ($this->getConfig()->hasHeader()) {
            $this->rewind();
            $this->getRecord();
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
     */
    private function isInvalidRecord(array $record): bool
    {
        return count($record) === 1 && $record[0] === null;
    }
}
