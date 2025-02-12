<?php

namespace Phpcsv\CsvHelper\Contracts;

use SplFileObject;

interface CsvReaderInterface
{
    /**
     * Returns the instance of the reader the CSV file.
     *
     * @return SplFileObject|null Returns the SplFileObject instance or null if the file does not exist.
     */
    public function getReader(): ?SplFileObject;

    /**
     * Returns the configuration of the CSV reader.
     *
     * @return CsvConfigInterface Returns the configuration instance.
     */
    public function getConfig(): CsvConfigInterface;

    /**
     * Returns the total number of records in the CSV file.
     *
     * @return int|null Returns the total number of records or null if the count cannot be determined.
     */
    public function getRecordCount(): ?int;

    /**
     * Moves the pointer to the next row of CSV data.
     */
    public function rewind(): void;

    /**
     * Returns the current position of the pointer.
     *
     * @return int Returns the current position of the pointer.
     */
    public function getCurrentPosition(): int;

    public function getRecord(): array|string|false;

    public function getHeader(): string|false|array;
}
