<?php

namespace CsvToolkit\Configs;

use CsvToolkit\Enums\Encoding;

/**
 * Configuration class for SplFileObject-based CSV operations.
 *
 * Provides configuration management for SplFileObject CSV handling
 * with basic CSV features supported by PHP's built-in functionality.
 */
class SplConfig
{
    protected string $delimiter = ',';

    protected string $enclosure = '"';

    protected string $escape = '\\';

    protected string $path = '';

    protected int $offset = 0;

    protected Encoding $encoding = Encoding::UTF8;

    protected bool $autoFlush = true;

    /**
     * Creates a new SplFileObject configuration instance.
     *
     * @param string|null $path Optional file path to set initially
     * @param bool $hasHeader Whether the CSV file has a header row (default: true)
     */
    public function __construct(?string $path = null, protected bool $hasHeader = true)
    {
        if ($path !== null) {
            $this->path = $path;
        }
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    public function getEscape(): string
    {
        return $this->escape;
    }

    public function setEscape(string $escape): self
    {
        $this->escape = $escape;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function hasHeader(): bool
    {
        return $this->hasHeader;
    }

    public function setHasHeader(bool $hasHeader): self
    {
        $this->hasHeader = $hasHeader;

        return $this;
    }

    public function getEncoding(): Encoding
    {
        return $this->encoding;
    }

    public function setEncoding(Encoding|int $encoding): self
    {
        if ($encoding instanceof Encoding) {
            $this->encoding = $encoding;
        } else {
            $this->encoding = Encoding::from($encoding);
        }

        return $this;
    }

    /**
     * Get the encoding as an enum.
     */
    public function getEncodingEnum(): Encoding
    {
        return $this->encoding;
    }

    /**
     * Checks if auto-flush is enabled for write operations.
     * Auto-flush causes data to be written to disk immediately after each write operation.
     * When disabled, data is buffered until flush() is called or the writer is closed.
     * Note: SplFileObject always writes immediately, so this setting has no effect.
     *
     * @return bool True if auto-flush is enabled (default: true)
     */
    public function getAutoFlush(): bool
    {
        return $this->autoFlush;
    }

    /**
     * Sets whether to automatically flush data after each write operation.
     * Note: SplFileObject always writes immediately, so this setting has no effect.
     * This method is provided for interface compatibility with CsvConfig.
     *
     * @param bool $autoFlush True to enable auto-flush (immediate writes),
     *                       false for manual flushing (better performance)
     * @return $this For method chaining
     */
    public function setAutoFlush(bool $autoFlush): self
    {
        $this->autoFlush = $autoFlush;

        return $this;
    }
}
