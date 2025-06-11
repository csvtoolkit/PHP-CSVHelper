<?php

namespace Phpcsv\CsvHelper\Configs;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;

/**
 * Abstract base class for CSV configuration implementations.
 *
 * Provides common properties and defines the abstract interface
 * that concrete configuration classes must implement.
 */
abstract class AbstractCsvConfig implements CsvConfigInterface
{
    /**
     * The field delimiter character.
     */
    protected string $delimiter = ',';

    /**
     * The field enclosure character.
     */
    protected string $enclosure = '"';

    /**
     * The escape character.
     */
    protected string $escape = '\\';

    /**
     * The file path.
     */
    protected string $path = '';

    /**
     * The starting offset for reading.
     */
    protected int $offset = 0;

    /**
     * Whether the CSV file has a header row.
     */
    protected bool $hasHeader = true;

    /**
     * Gets the field delimiter character.
     *
     * @return string The delimiter character
     */
    abstract public function getDelimiter(): string;

    /**
     * Sets the field delimiter character.
     *
     * @param string $delimiter The delimiter character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setDelimiter(string $delimiter): CsvConfigInterface;

    /**
     * Gets the field enclosure character.
     *
     * @return string The enclosure character
     */
    abstract public function getEnclosure(): string;

    /**
     * Sets the field enclosure character.
     *
     * @param string $enclosure The enclosure character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setEnclosure(string $enclosure): CsvConfigInterface;

    /**
     * Gets the escape character.
     *
     * @return string The escape character
     */
    abstract public function getEscape(): string;

    /**
     * Sets the escape character.
     *
     * @param string $escape The escape character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setEscape(string $escape): CsvConfigInterface;

    /**
     * Gets the file path.
     *
     * @return string The file path
     */
    abstract public function getPath(): string;

    /**
     * Sets the file path.
     *
     * @param string $path The file path to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setPath(string $path): CsvConfigInterface;

    /**
     * Gets the starting offset for reading.
     *
     * @return int The offset
     */
    abstract public function getOffset(): int;

    /**
     * Sets the starting offset for reading.
     *
     * @param int $offset The offset to start reading from
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setOffset(int $offset): CsvConfigInterface;

    /**
     * Checks if the CSV file has a header row.
     *
     * @return bool True if the file has headers, false otherwise
     */
    abstract public function hasHeader(): bool;

    /**
     * Sets whether the CSV file has a header row.
     *
     * @param bool $hasHeader True if the file has headers, false otherwise
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    abstract public function setHasHeader(bool $hasHeader): CsvConfigInterface;
}
