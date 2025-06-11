<?php

/**
 * FastCSV Extension Stubs
 * @version 0.0.1
 */

// Only declare classes if the FastCSV extension is not loaded
if (!extension_loaded('fastcsv')) {

/**
 * Class FastCSVConfig
 * 
 * Configuration class for CSV processing settings.
 */
class FastCSVConfig {
    /**
     * Creates a new CSV configuration with default values
     */
    public function __construct() {}

    /**
     * Gets the field delimiter character
     * 
     * @return string The delimiter character (default: ',')
     */
    public function getDelimiter(): string {}

    /**
     * Sets the field delimiter character
     * 
     * @param string $delimiter Single character delimiter
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setDelimiter(string $delimiter): FastCSVConfig {
        return $this;
    }

    /**
     * Gets the field enclosure character
     * 
     * @return string The enclosure character (default: '"')
     */
    public function getEnclosure(): string {}

    /**
     * Sets the field enclosure character
     * 
     * @param string $enclosure Single character enclosure
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setEnclosure(string $enclosure): FastCSVConfig {
        return $this;
    }

    /**
     * Gets the escape character
     * 
     * @return string The escape character (default: '\\')
     */
    public function getEscape(): string {}

    /**
     * Sets the escape character
     * 
     * @param string $escape Single character escape
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setEscape(string $escape): FastCSVConfig {
        return $this;
    }

    /**
     * Gets the CSV file path
     * 
     * @return string The file path
     */
    public function getPath(): string {}

    /**
     * Sets the CSV file path
     * 
     * @param string $path Path to the CSV file
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setPath(string $path): FastCSVConfig {
        return $this;
    }

    /**
     * Gets the number of lines to skip at the beginning
     * 
     * @return int The offset (default: 0)
     */
    public function getOffset(): int {}

    /**
     * Sets the number of lines to skip at the beginning
     * 
     * @param int $offset Number of lines to skip
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setOffset(int $offset): FastCSVConfig {
        return $this;
    }

    /**
     * Checks if the CSV has headers
     * 
     * @return bool True if CSV has headers (default: true)
     */
    public function hasHeader(): bool {}

    /**
     * Sets whether the CSV has headers
     * 
     * @param bool $hasHeader True if CSV has headers
     * @return FastCSVConfig Returns self for method chaining
     */
    public function setHasHeader(bool $hasHeader): FastCSVConfig {
        return $this;
    }
}

/**
 * Class FastCSVReader
 * 
 * A high-performance CSV file reader.
 */
class FastCSVReader {
    /**
     * Creates a new CSV reader instance
     * 
     * @param FastCSVConfig $config Configuration object containing file path and parsing options
     * @throws Exception If the file cannot be opened
     */
    public function __construct(FastCSVConfig $config) {}

    /**
     * Returns the CSV headers (first row)
     * 
     * @return array|false Array of header strings, or false if no headers
     */
    public function getHeaders() {}

    /**
     * Returns the next record from the CSV file
     * 
     * @return array|false Array of strings representing the record, or false if EOF
     */
    public function nextRecord(): array|false {}

    /**
     * Closes the reader and frees resources
     * 
     * @return void
     */
    public function close(): void {}

    /**
     * Rewinds the reader to the beginning of the data records
     * Position will be -1 if hasHeaders is true, 0 otherwise
     * 
     * @return void
     */
    public function rewind(): void {}

    /**
     * Sets a new configuration and reloads the file
     * 
     * @param FastCSVConfig $config New configuration
     * @return bool True on success, false on failure
     */
    public function setConfig(FastCSVConfig $config): bool {}

    /**
     * Gets the total number of data records in the file
     * Result is cached after first call
     * 
     * @return int Total record count, or -1 on error
     */
    public function getRecordCount(): int {}

    /**
     * Gets the current position relative to data records
     * Returns -1 if hasHeaders and before first record, 0+ for record positions
     * 
     * @return int Current position, or -1 on error
     */
    public function getPosition(): int {}

    /**
     * Seeks to a specific record position and returns the record
     * Position is relative to data records (excludes header if present)
     * 
     * @param int $position Zero-based position to seek to
     * @return array|false Record data on success, false if position is invalid or on error
     */
    public function seek(int $position): array|false {}

    /**
     * Checks if there are more records available from current position
     * 
     * @return bool True if more records exist, false if at end of file or error
     */
    public function hasNext(): bool {}
}

/**
 * Class FastCSVWriter
 * 
 * A high-performance CSV file writer.
 */
class FastCSVWriter {
    /**
     * Creates a new CSV writer instance
     * 
     * @param FastCSVConfig $config Configuration object containing file path and parsing options
     * @param array $headers Array of header strings
     * @throws Exception If the file cannot be created
     */
    public function __construct(FastCSVConfig $config, array $headers) {}

    /**
     * Writes a record to the CSV file
     * 
     * @param array $record Array of field values to write
     * @return bool True on success, false on failure
     */
    public function writeRecord(array $record) {}

    /**
     * Writes a record using an associative array mapped to headers
     * 
     * @param array $fieldsMap Associative array mapping header names to values
     * @return bool True on success, false on failure
     */
    public function writeRecordMap(array $fieldsMap) {}

    /**
     * Closes the writer and frees resources
     * 
     * @return void
     */
    public function close(): void {}
}

} // End of extension_loaded check 