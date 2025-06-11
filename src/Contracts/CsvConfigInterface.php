<?php

namespace CsvToolkit\Contracts;

/**
 * Interface for CSV configuration management.
 *
 * Defines the contract for managing CSV parsing and formatting configuration
 * including delimiters, enclosures, escape characters, file paths, and headers.
 */
interface CsvConfigInterface
{
    /**
     * Gets the field delimiter character.
     *
     * @return string The delimiter character (default: ',')
     */
    public function getDelimiter(): string;

    /**
     * Sets the field delimiter character.
     *
     * @param string $delimiter The delimiter character to use
     * @return self Returns the instance for method chaining
     */
    public function setDelimiter(string $delimiter): self;

    /**
     * Gets the field enclosure character.
     *
     * @return string The enclosure character (default: '"')
     */
    public function getEnclosure(): string;

    /**
     * Sets the field enclosure character.
     *
     * @param string $enclosure The enclosure character to use
     * @return self Returns the instance for method chaining
     */
    public function setEnclosure(string $enclosure): self;

    /**
     * Gets the escape character.
     *
     * @return string The escape character (default: '\')
     */
    public function getEscape(): string;

    /**
     * Sets the escape character.
     *
     * @param string $escape The escape character to use
     * @return self Returns the instance for method chaining
     */
    public function setEscape(string $escape): self;

    /**
     * Gets the file path.
     *
     * @return string The file path
     */
    public function getPath(): string;

    /**
     * Sets the file path.
     *
     * @param string $path The file path to use
     * @return self Returns the instance for method chaining
     */
    public function setPath(string $path): self;

    /**
     * Gets the starting offset for reading.
     *
     * @return int The offset (default: 0)
     */
    public function getOffset(): int;

    /**
     * Sets the starting offset for reading.
     *
     * @param int $offset The offset to start reading from
     * @return self Returns the instance for method chaining
     */
    public function setOffset(int $offset): self;

    /**
     * Checks if the CSV file has a header row.
     *
     * @return bool True if the file has headers, false otherwise
     */
    public function hasHeader(): bool;

    /**
     * Sets whether the CSV file has a header row.
     *
     * @param bool $hasHeader True if the file has headers, false otherwise
     * @return self Returns the instance for method chaining
     */
    public function setHasHeader(bool $hasHeader): self;
}
