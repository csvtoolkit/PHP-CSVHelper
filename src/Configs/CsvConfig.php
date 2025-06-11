<?php

namespace Phpcsv\CsvHelper\Configs;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;

/**
 * Concrete implementation of CSV configuration.
 *
 * Provides a complete implementation of the CsvConfigInterface with
 * default values and fluent interface for configuration management.
 */
class CsvConfig extends AbstractCsvConfig
{
    /**
     * Creates a new CSV configuration instance.
     *
     * @param string|null $path Optional file path to set initially
     * @param bool $hasHeader Whether the CSV file has a header row (default: true)
     */
    public function __construct(?string $path = null, bool $hasHeader = true)
    {
        if ($path !== null) {
            $this->path = $path;
        }
        $this->hasHeader = $hasHeader;
    }

    /**
     * Gets the field delimiter character.
     *
     * @return string The delimiter character
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Sets the field delimiter character.
     *
     * @param string $delimiter The delimiter character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setDelimiter(string $delimiter): CsvConfigInterface
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Gets the field enclosure character.
     *
     * @return string The enclosure character
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Sets the field enclosure character.
     *
     * @param string $enclosure The enclosure character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setEnclosure(string $enclosure): CsvConfigInterface
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Gets the escape character.
     *
     * @return string The escape character
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * Sets the escape character.
     *
     * @param string $escape The escape character to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setEscape(string $escape): CsvConfigInterface
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Gets the file path.
     *
     * @return string The file path
     */
    public function getPath(): string
    {
        return $this->path;

    }

    /**
     * Sets the file path.
     *
     * @param string $path The file path to use
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setPath(string $path): CsvConfigInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Gets the starting offset for reading.
     *
     * @return int The offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Sets the starting offset for reading.
     *
     * @param int $offset The offset to start reading from
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setOffset(int $offset): CsvConfigInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Checks if the CSV file has a header row.
     *
     * @return bool True if the file has headers, false otherwise
     */
    public function hasHeader(): bool
    {
        return $this->hasHeader;
    }

    /**
     * Sets whether the CSV file has a header row.
     *
     * @param bool $hasHeader True if the file has headers, false otherwise
     * @return CsvConfigInterface Returns the instance for method chaining
     */
    public function setHasHeader(bool $hasHeader): CsvConfigInterface
    {
        $this->hasHeader = $hasHeader;

        return $this;
    }
}
