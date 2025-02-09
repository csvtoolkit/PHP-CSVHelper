<?php

namespace Phpcsv\CsvHelper\Readers;

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
    /**
     * Gets or initializes the SplFileObject reader
     *
     * @return SplFileObject|null The configured file object
     *
     * @throws FileNotFoundException When the file does not exist
     * @throws FileNotReadableException When the file is not readable
     * @throws EmptyFileException When the file is empty
     */
    public function getReader(): ?SplFileObject
    {
        if (! $this->reader instanceof SplFileObject) {
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
        }

        return $this->reader;
    }

    /**
     * Gets the total number of records in the CSV file
     *
     * Note: This method needs to seek through the entire file to count records,
     * which may be expensive for large files.
     *
     * @return int|null The total number of records, or null if counting failed
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

    /**
     * Rewinds the reader to the beginning of the file
     */
    public function rewind(): void
    {
        if (! $this->reader instanceof SplFileObject) {
            return;
        }
        $this->position = 0;
        $this->reader->rewind();
    }

    /**
     * Reads the current record from the CSV file
     *
     * @return array|false Returns an array containing the fields of the current record,
     *                     or false if end of file is reached or an error occurs
     */
    public function getRecord(): array|false
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();

        $reader->seek($this->getCurrentPosition());
        $record = $reader->current();

        if ($record !== false) {
            $this->position++;
        }

        return is_array($record) ? $record : false;
    }

    /**
     * Gets the current position in the file
     *
     * @return int The current record position (0-based)
     */
    public function getCurrentPosition(): int
    {
        return $this->position;
    }

    /**
     * Retrieves the header row if configured
     *
     * @return array|false Returns the header row as an array if headers are enabled,
     *                     false if headers are disabled or an error occurs
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

    public function seek(int $position): array|false
    {
        /** @var SplFileObject $reader */
        $reader = $this->getReader();
        $this->position = $position - 1; // -1 because we increment the position after reading a record
        $reader->seek($position);

        return $this->getRecord();
    }
}
