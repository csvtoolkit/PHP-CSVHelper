<?php

namespace CsvToolkit\Configs;

use CsvToolkit\Enums\Encoding;
use FastCSVConfig;

/**
 * Configuration class for FastCSV extension.
 *
 * Provides configuration management specifically for the FastCSV extension
 * including all advanced features like encoding, BOM, strict mode, etc.
 */
class CsvConfig
{
    protected string $delimiter = ',';

    protected string $enclosure = '"';

    protected string $escape = '\\';

    protected string $path = '';

    protected int $offset = 0;

    protected Encoding $encoding = Encoding::UTF8;

    protected bool $writeBOM = false;

    protected bool $strictMode = true;

    protected bool $skipEmptyLines = false;

    protected bool $trimFields = false;

    protected bool $preserveQuotes = false;

    protected bool $autoFlush = true;

    /**
     * Creates a new FastCSV configuration instance.
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

    public function getEncoding(): int
    {
        return $this->encoding->value;
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

    public function getWriteBOM(): bool
    {
        return $this->writeBOM;
    }

    public function setWriteBOM(bool $writeBOM): self
    {
        $this->writeBOM = $writeBOM;

        return $this;
    }

    public function getStrictMode(): bool
    {
        return $this->strictMode;
    }

    public function setStrictMode(bool $strictMode): self
    {
        $this->strictMode = $strictMode;

        return $this;
    }

    public function getSkipEmptyLines(): bool
    {
        return $this->skipEmptyLines;
    }

    public function setSkipEmptyLines(bool $skipEmptyLines): self
    {
        $this->skipEmptyLines = $skipEmptyLines;

        return $this;
    }

    public function getTrimFields(): bool
    {
        return $this->trimFields;
    }

    public function setTrimFields(bool $trimFields): self
    {
        $this->trimFields = $trimFields;

        return $this;
    }

    public function getPreserveQuotes(): bool
    {
        return $this->preserveQuotes;
    }

    public function setPreserveQuotes(bool $preserveQuotes): self
    {
        $this->preserveQuotes = $preserveQuotes;

        return $this;
    }

    /**
     * Checks if auto-flush is enabled.
     * Auto-flush causes data to be written to disk immediately after each write operation.
     * When disabled, data is buffered until flush() is called or the writer is closed.
     *
     * @return bool True if auto-flush is enabled (default: true)
     */
    public function getAutoFlush(): bool
    {
        return $this->autoFlush;
    }

    /**
     * Sets whether to automatically flush data after each write.
     *
     * @param bool $autoFlush True to enable auto-flush, false for manual flushing
     * @return $this
     */
    public function setAutoFlush(bool $autoFlush): self
    {
        $this->autoFlush = $autoFlush;

        return $this;
    }

    /**
     * Creates a FastCSVConfig object for the extension.
     *
     * @return \FastCSVConfig The native extension config object
     */
    public function toFastCsvConfig(): FastCSVConfig
    {
        $config = new FastCSVConfig();
        $config
            ->setPath($this->path)
            ->setDelimiter($this->delimiter)
            ->setEnclosure($this->enclosure)
            ->setEscape($this->escape)
            ->setHasHeader($this->hasHeader)
            ->setOffset($this->offset)
            ->setEncoding($this->encoding->value)
            ->setWriteBOM($this->writeBOM)
            ->setStrictMode($this->strictMode)
            ->setSkipEmptyLines($this->skipEmptyLines)
            ->setTrimFields($this->trimFields)
            ->setPreserveQuotes($this->preserveQuotes)
            ->setAutoFlush($this->autoFlush);

        return $config;
    }
}
