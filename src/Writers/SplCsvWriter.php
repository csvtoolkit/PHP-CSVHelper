<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Exceptions\InvalidConfigurationException;
use SplFileObject;

/**
 * CSV Writer implementation using SplFileObject
 *
 * This class provides functionality to write CSV files using PHP's built-in SplFileObject.
 * It supports custom delimiters, enclosures, and escape characters.
 */
class SplCsvWriter extends AbstractCsvWriter
{
    /**
     * Creates a new SplFileObject-based CSV writer instance.
     *
     * @param string|null $target Optional file path for output
     * @param CsvConfigInterface|null $config Optional configuration object
     */
    public function __construct(
        ?string $target = null,
        ?CsvConfigInterface $config = null
    ) {
        $this->config = $config ?? new CsvConfig();

        if ($target !== null) {
            $this->setTarget($target);
        }
    }

    /**
     * Gets the underlying SplFileObject instance.
     *
     * @return SplFileObject|null The SplFileObject instance
     * @throws InvalidConfigurationException If configuration is invalid
     * @throws FileNotFoundException If directory doesn't exist or file cannot be created
     */
    public function getWriter(): ?SplFileObject
    {
        if (! $this->writer instanceof SplFileObject) {
            $this->validateConfig();

            try {
                $targetPath = $this->getTarget();
                $directory = dirname($targetPath);

                // Check if directory exists
                if (! is_dir($directory)) {
                    throw new FileNotFoundException("Directory does not exist: $directory");
                }

                $this->writer = new SplFileObject($targetPath, 'w');
                $this->writer->setCsvControl(
                    $this->getConfig()->getDelimiter(),
                    $this->getConfig()->getEnclosure(),
                    $this->getConfig()->getEscape()
                );
            } catch (\RuntimeException $e) {
                throw new FileNotFoundException("Failed to open file for writing: " . $this->getTarget(), 0, $e);
            } catch (\Exception $e) {
                // Catch any other exceptions and convert to FileNotFoundException
                throw new FileNotFoundException("Failed to open file for writing: " . $this->getTarget(), 0, $e);
            }
        }

        return $this->writer;
    }

    /**
     * Gets the current CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    public function getConfig(): CsvConfigInterface
    {
        if (! isset($this->config)) {
            $this->config = new CsvConfig();
        }

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
     * Sets the output file path.
     *
     * @param string $target File path for CSV output
     */
    public function setTarget(string $target): void
    {
        $this->config->setPath($target);
    }

    /**
     * Gets the current output file path.
     *
     * @return string File path string
     */
    public function getTarget(): string
    {
        return $this->config->getPath();
    }

    /**
     * Gets the source file path.
     *
     * @return string File path string
     */
    public function getSource(): string
    {
        return $this->getTarget();
    }

    /**
     * Sets the source file path.
     *
     * @param string $source File path to set
     */
    public function setSource(string $source): void
    {
        $this->setTarget($source);
    }

    /**
     * Sets the CSV configuration.
     *
     * @param CsvConfigInterface $config New configuration
     */
    public function setConfig(CsvConfigInterface $config): void
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
}
