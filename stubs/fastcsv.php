<?php

/**
 * FastCSV Extension Stubs
 * @version 0.0.1
 */

/**
 * Character encoding constants
 */
if (!defined('CSV_ENCODING_UTF8')) {
    define('CSV_ENCODING_UTF8', 0);
    define('CSV_ENCODING_UTF16LE', 1);
    define('CSV_ENCODING_UTF16BE', 2);
    define('CSV_ENCODING_UTF32LE', 3);
    define('CSV_ENCODING_UTF32BE', 4);
    define('CSV_ENCODING_ASCII', 5);
    define('CSV_ENCODING_LATIN1', 6);
}

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
    public function getDelimiter(): string {
        return '';
    }

    /**
     * Sets the field delimiter character
     * 
     * @param string $delimiter Single character delimiter
     * @return $this
     */
    public function setDelimiter(string $delimiter): self {
        return $this;
    }

    /**
     * Gets the field enclosure character
     * 
     * @return string The enclosure character (default: '"')
     */
    public function getEnclosure(): string {
        return '';
    }

    /**
     * Sets the field enclosure character
     * 
     * @param string $enclosure Single character enclosure
     * @return $this
     */
    public function setEnclosure(string $enclosure): self {
        return $this;
    }

    /**
     * Gets the escape character
     * 
     * @return string The escape character (default: '\\')
     */
    public function getEscape(): string {
        return '';
    }

    /**
     * Sets the escape character
     * 
     * @param string $escape Single character escape
     * @return $this
     */
    public function setEscape(string $escape): self {
        return $this;
    }

    /**
     * Gets the CSV file path
     * 
     * @return string The file path
     */
    public function getPath(): string {
        return '';
    }

    /**
     * Sets the CSV file path
     * 
     * @param string $path Path to the CSV file
     * @return $this
     */
    public function setPath(string $path): self {
        return $this;
    }

    /**
     * Gets the number of lines to skip at the beginning
     * 
     * @return int The offset (default: 0)
     */
    public function getOffset(): int {
        return 0;
    }

    /**
     * Sets the number of lines to skip at the beginning
     * 
     * @param int $offset Number of lines to skip
     * @return $this
     */
    public function setOffset(int $offset): self {
        return $this;
    }

    /**
     * Checks if the CSV has headers
     * 
     * @return bool True if CSV has headers (default: true)
     */
    public function hasHeader(): bool {
        return true;
    }

    /**
     * Sets whether the CSV has headers
     * 
     * @param bool $hasHeader True if CSV has headers
     * @return $this
     */
    public function setHasHeader(bool $hasHeader): self {
        return $this;
    }

    /**
     * Gets the character encoding
     * 
     * @return int The encoding constant (default: CSV_ENCODING_UTF8)
     */
    public function getEncoding(): int {
        return CSV_ENCODING_UTF8;
    }

    /**
     * Sets the character encoding
     * 
     * @param int $encoding One of the CSV_ENCODING_* constants
     * @return $this
     */
    public function setEncoding(int $encoding): self {
        return $this;
    }

    /**
     * Checks if BOM (Byte Order Mark) should be written
     * 
     * @return bool True if BOM should be written (default: false)
     */
    public function getWriteBOM(): bool {
        return false;
    }

    /**
     * Sets whether to write BOM (Byte Order Mark)
     * 
     * @param bool $writeBOM True to write BOM
     * @return $this
     */
    public function setWriteBOM(bool $writeBOM): self {
        return $this;
    }

    /**
     * Checks if strict mode is enabled
     * 
     * @return bool True if strict mode is enabled (default: true)
     */
    public function getStrictMode(): bool {
        return true;
    }

    /**
     * Sets whether to use strict mode
     * In strict mode, fields with spaces are always quoted
     * 
     * @param bool $strictMode True to enable strict mode
     * @return $this
     */
    public function setStrictMode(bool $strictMode): self {
        return $this;
    }

    /**
     * Checks if empty lines should be skipped
     * 
     * @return bool True if empty lines should be skipped (default: false)
     */
    public function getSkipEmptyLines(): bool {
        return false;
    }

    /**
     * Sets whether to skip empty lines
     * 
     * @param bool $skipEmptyLines True to skip empty lines
     * @return $this
     */
    public function setSkipEmptyLines(bool $skipEmptyLines): self {
        return $this;
    }

    /**
     * Checks if fields should be trimmed
     * 
     * @return bool True if fields should be trimmed (default: false)
     */
    public function getTrimFields(): bool {
        return false;
    }

    /**
     * Sets whether to trim fields
     * 
     * @param bool $trimFields True to trim fields
     * @return $this
     */
    public function setTrimFields(bool $trimFields): self {
        return $this;
    }

    /**
     * Checks if quotes should be preserved
     * 
     * @return bool True if quotes should be preserved (default: false)
     */
    public function getPreserveQuotes(): bool {
        return false;
    }

    /**
     * Sets whether to preserve quotes
     * 
     * @param bool $preserveQuotes True to preserve quotes
     * @return $this
     */
    public function setPreserveQuotes(bool $preserveQuotes): self {
        return $this;
    }

    /**
     * Checks if auto-flush is enabled
     * Auto-flush causes data to be written to disk immediately after each write operation.
     * When disabled, data is buffered until flush() is called or the writer is closed.
     * 
     * @return bool True if auto-flush is enabled (default: true)
     */
    public function getAutoFlush(): bool {
        return true;
    }

    /**
     * Sets whether to automatically flush data after each write
     * 
     * @param bool $autoFlush True to enable auto-flush, false for manual flushing
     * @return $this
     */
    public function setAutoFlush(bool $autoFlush): self {
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
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(FastCSVConfig $config) {}

    /**
     * Returns the CSV headers (first row)
     * 
     * @return array|false Array of header strings, or false if no headers
     */
    public function getHeaders(): array|false {
        return [];
    }

    /**
     * Returns the next record from the CSV file
     * 
     * @return array|false|null Array of strings representing the record, false if EOF, or null if malformed data
     */
    public function nextRecord(): array|false|null {
        return [];
    }

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
    public function setConfig(FastCSVConfig $config): bool {
        return true;
    }

    /**
     * Gets the total number of data records in the file
     * Result is cached after first call
     * 
     * @return int Total record count, or -1 on error
     */
    public function getRecordCount(): int {
        return 0;
    }

    /**
     * Gets the current position relative to data records
     * Returns -1 if hasHeaders and before first record, 0+ for record positions
     * 
     * @return int Current position, or -1 on error
     */
    public function getPosition(): int {
        return 0;
    }

    /**
     * Seeks to a specific record position and returns the record
     * Position is relative to data records (excludes header if present)
     * 
     * @param int $position Zero-based position to seek to
     * @return array|false Record data on success, false if position is invalid or on error
     */
    public function seek(int $position): array|false {
        return [];
    }

    /**
     * Checks if there are more records available from current position
     * 
     * @return bool True if more records exist, false if at end of file or error
     */
    public function hasNext(): bool {
        return false;
    }
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
     * @phpstan-ignore constructor.unusedParameter
     */
    public function __construct(FastCSVConfig $config, array $headers) {}

    /**
     * Writes a record to the CSV file
     * 
     * @param array $record Array of strings representing the record
     * @return bool True on success, false on failure
     */
    public function writeRecord(array $record): bool {
        return true;
    }

    /**
     * Writes a record using field names and values
     * 
     * @param array $fieldsMap Associative array of field names and values
     * @return bool True on success, false on failure
     */
    public function writeRecordMap(array $fieldsMap): bool {
        return true;
    }

    /**
     * Manually flushes buffered data to disk
     * This method is useful when auto-flush is disabled for performance reasons.
     * 
     * @return bool True on success, false on failure
     * @throws Exception If writer is not initialized or flush operation fails
     */
    public function flush(): bool {
        return true;
    }

    /**
     * Closes the writer and frees resources
     * 
     * @return void
     */
    public function close(): void {}
}

} // End of extension_loaded check 