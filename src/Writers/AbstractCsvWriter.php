<?php

namespace CsvToolkit\Writers;

use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Contracts\CsvWriterInterface;
use FastCSVWriter;
use SplFileObject;

/**
 * Abstract base class for CSV writers.
 *
 * Provides common functionality and properties for CSV writer implementations.
 * Concrete implementations must handle the specific writing logic for their
 * underlying libraries (e.g., FastCSV extension or SplFileObject).
 */
abstract class AbstractCsvWriter implements CsvWriterInterface
{
    /**
     * Header row data.
     */
    protected ?array $header = null;

    /**
     * The CSV configuration object.
     */
    protected CsvConfigInterface $config;

    /**
     * The underlying writer object (SplFileObject or FastCSVWriter).
     */
    protected SplFileObject|FastCSVWriter|null $writer = null;

    /**
     * Creates a new CSV writer instance.
     *
     * @param string|null $target Optional file path for output
     * @param CsvConfigInterface|null $config Optional configuration object
     */
    abstract public function __construct(
        ?string $target = null,
        ?CsvConfigInterface $config = null
    );

    /**
     * Gets the underlying writer object.
     *
     * @return SplFileObject|FastCSVWriter|null The writer object
     */
    abstract public function getWriter(): SplFileObject|FastCSVWriter|null;

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    abstract public function getConfig(): CsvConfigInterface;

    /**
     * Writes a single record to the CSV file.
     *
     * @param array $data Array of field values to write
     */
    abstract public function write(array $data): void;

    /**
     * Sets the output file path.
     *
     * @param string $target File path for CSV output
     */
    abstract public function setTarget(string $target): void;

    /**
     * Gets the current output file path.
     *
     * @return string File path string
     */
    abstract public function getTarget(): string;
}
