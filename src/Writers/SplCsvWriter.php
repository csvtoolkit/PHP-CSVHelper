<?php

namespace CsvToolkit\Writers;

use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Exceptions\CsvWriterException;
use CsvToolkit\Exceptions\DirectoryNotFoundException;
use CsvToolkit\Exceptions\InvalidConfigurationException;
use CsvToolkit\Helpers\FileValidator;
use FastCSVWriter;
use SplFileObject;

/**
 * CSV Writer implementation using SplFileObject
 *
 * This class provides CSV writing functionality using PHP's built-in SplFileObject.
 * It serves as a fallback when the FastCSV extension is not available.
 */
class SplCsvWriter implements CsvWriterInterface
{
    protected ?array $header = null;

    protected SplConfig $config;

    protected SplFileObject|FastCSVWriter|null $writer = null;

    /**
     * Creates a new SplFileObject-based CSV writer instance.
     *
     * @param string|null $target Optional file path for output
     * @param SplConfig|null $config Optional configuration object
     */
    public function __construct(
        ?string $target = null,
        ?SplConfig $config = null
    ) {
        $this->config = $config ?? new SplConfig();

        if ($target !== null) {
            $this->setTarget($target);
        }
    }

    /**
     * Gets the underlying SplFileObject instance.
     *
     * @return SplFileObject|FastCSVWriter|null The SplFileObject instance
     * @throws InvalidConfigurationException If configuration is invalid
     * @throws CsvWriterException If target path is empty or file cannot be created
     * @throws DirectoryNotFoundException If directory doesn't exist
     */
    public function getWriter(): SplFileObject|FastCSVWriter|null
    {
        if (! $this->writer instanceof SplFileObject) {
            $this->validateConfig();

            $targetPath = $this->getDestination();
            FileValidator::validateFileWritable($targetPath);

            try {
                $this->writer = new SplFileObject($targetPath, 'w');
                $this->writer->setCsvControl(
                    $this->getConfig()->getDelimiter(),
                    $this->getConfig()->getEnclosure(),
                    $this->getConfig()->getEscape()
                );
            } catch (\RuntimeException $e) {
                throw new CsvWriterException("Failed to open file for writing: " . $this->getDestination(), 0, $e);
            } catch (\Exception $e) {
                // Catch any other exceptions and convert to CsvWriterException
                throw new CsvWriterException("Failed to open file for writing: " . $this->getDestination(), 0, $e);
            }
        }

        return $this->writer;
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return SplConfig The configuration object
     */
    public function getConfig(): SplConfig
    {
        return $this->config;
    }

    /**
     * Writes a single record to the CSV file.
     *
     * @param array $data Array of field values to write
     * @throws InvalidConfigurationException If configuration is invalid
     */
    public function write(array $data): void
    {
        /** @var SplFileObject $writer */
        $writer = $this->getWriter();

        // Convert all values to string and handle escaping if needed
        $data = $this->prepareData($data);

        $writer->fputcsv(
            $data,
            $this->getConfig()->getDelimiter(),
            $this->getConfig()->getEnclosure(),
            $this->getConfig()->getEscape()
        );
    }

    /**
     * Prepares data for CSV writing by converting to strings and handling escaping.
     *
     * @param array $data Raw data array
     * @return array Prepared data array with string values
     */
    private function prepareData(array $data): array
    {
        $data = array_map($this->convertToString(...), $data);

        return array_map(fn (string $value): string => $this->shouldEscape($value) ? $this->escapeValue($value) : $value, $data);
    }

    /**
     * Determines if a value needs escaping.
     *
     * @param string $value The value to check
     * @return bool True if the value needs escaping, false otherwise
     */
    private function shouldEscape(string $value): bool
    {
        return str_contains($value, $this->getConfig()->getEnclosure()) ||
               str_contains($value, $this->getConfig()->getDelimiter()) ||
               str_contains($value, "\n") ||
               str_contains($value, "\r");
    }

    /**
     * Escapes a value according to CSV rules.
     *
     * @param string $value The value to escape
     * @return string The escaped value
     */
    private function escapeValue(string $value): string
    {
        $enclosure = $this->getConfig()->getEnclosure();

        return str_replace(
            $enclosure,
            $this->getConfig()->getEscape().$enclosure,
            $value
        );
    }

    /**
     * Validates CSV configuration.
     *
     * @throws InvalidConfigurationException If any configuration parameter is invalid
     */
    private function validateConfig(): void
    {
        if (strlen($this->getConfig()->getEnclosure()) !== 1) {
            throw new InvalidConfigurationException(
                'CSV enclosure must be a single character'
            );
        }

        if (strlen($this->getConfig()->getDelimiter()) !== 1) {
            throw new InvalidConfigurationException(
                'CSV delimiter must be a single character'
            );
        }

        if (strlen($this->getConfig()->getEscape()) !== 1) {
            throw new InvalidConfigurationException(
                'CSV escape character must be a single character'
            );
        }
    }

    /**
     * Converts a mixed value to a string.
     *
     * @param mixed $value The value to convert
     * @return string The converted string
     */
    public function convertToString(mixed $value): string
    {
        if (is_array($value)) {
            $json = json_encode($value);

            return $json !== false ? $json : '';
        }

        if (is_object($value) && ! method_exists($value, '__toString')) {
            $json = json_encode($value);

            return $json !== false ? $json : '';
        }

        if ($value === null || is_resource($value)) {
            return '';
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        return '';
    }

    /**
     * Sets the CSV configuration.
     *
     * @param SplConfig $config New configuration
     */
    public function setConfig(SplConfig $config): void
    {
        $this->config = $config;
        // Reset writer to apply new config
        $this->writer = null;
    }

    /**
     * Writes all records to the CSV file at once.
     *
     * @param array<int, array> $records Array of records to write
     * @throws InvalidConfigurationException If configuration is invalid
     */
    public function writeAll(array $records): void
    {
        foreach ($records as $record) {
            if (! is_array($record)) {
                throw new \InvalidArgumentException('Each record must be an array');
            }
            $this->write($record);
        }
    }

    /**
     * Sets the CSV headers.
     *
     * @param array $headers Array of header strings
     */
    public function setHeaders(array $headers): void
    {
        $this->header = $headers;
    }

    /**
     * Gets the CSV headers.
     *
     * @return array|null Array of header strings or null if not set
     */
    public function getHeaders(): ?array
    {
        return $this->header;
    }

    /**
     * Sets the destination file path.
     *
     * @param string $destination Path to write CSV file
     */
    public function setDestination(string $destination): void
    {
        $this->config->setPath($destination);
    }

    /**
     * Gets the current destination file path.
     *
     * @return string File path
     */
    public function getDestination(): string
    {
        return $this->config->getPath();
    }

    /**
     * Sets the output file path (alias for setDestination).
     *
     * @param string $target File path for CSV output
     */
    public function setTarget(string $target): void
    {
        $this->setDestination($target);
    }

    /**
     * Gets the current output file path (alias for getDestination).
     *
     * @return string File path string
     */
    public function getTarget(): string
    {
        return $this->getDestination();
    }

    /**
     * Gets the source file path (alias for getDestination).
     *
     * @return string File path string
     */
    public function getSource(): string
    {
        return $this->getDestination();
    }

    /**
     * Sets the source file path (alias for setDestination).
     *
     * @param string $source File path to set
     */
    public function setSource(string $source): void
    {
        $this->setDestination($source);
    }

    /**
     * Manually flushes buffered data to disk.
     * This method is useful when auto-flush is disabled for performance reasons.
     * Call this periodically (e.g., every 1000 records) to ensure data is written.
     *
     * @return bool True on success, false on failure
     */
    public function flush(): bool
    {
        // SplFileObject writes immediately, so flush always succeeds
        // This method is provided for interface compatibility with CsvWriter
        return true;
    }

    /**
     * Closes the writer and frees resources.
     */
    public function close(): void
    {
        // SplFileObject automatically closes when it goes out of scope
        // No explicit close needed for SplFileObject
    }
}
