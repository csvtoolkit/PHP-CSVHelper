<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
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
     * @throws InvalidConfigurationException
     */
    public function getWriter(): ?SplFileObject
    {
        if (! $this->writer instanceof SplFileObject) {
            $this->validateConfig();
            $this->writer = new SplFileObject($this->getTarget(), 'w');
            $this->writer->setCsvControl(
                $this->getConfig()->getDelimiter(),
                $this->getConfig()->getEnclosure(),
                $this->getConfig()->getEscape()
            );
        }

        return $this->writer;
    }

    /**
     * Gets the CSV configuration object
     */
    public function getConfig(): CsvConfigInterface
    {
        if (! isset($this->config)) {
            $this->config = new CsvConfig();
        }

        return $this->config;
    }

    /**
     * Writes a single record to the CSV file
     *
     * @throws InvalidConfigurationException
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
     * Prepares data for CSV writing by converting to strings and handling escaping
     */
    private function prepareData(array $data): array
    {
        // @noRector
        $data = array_map($this->convertToString(...), $data);

        return array_map(fn (string $value): string => $this->shouldEscape($value) ? $this->escapeValue($value) : $value, $data);
    }

    /**
     * Determines if a value needs escaping
     */
    private function shouldEscape(string $value): bool
    {
        return str_contains($value, $this->getConfig()->getEnclosure()) ||
               str_contains($value, $this->getConfig()->getDelimiter()) ||
               str_contains($value, "\n") ||
               str_contains($value, "\r");
    }

    /**
     * Escapes a value according to CSV rules
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
     * Validates CSV configuration
     *
     * @throws InvalidConfigurationException
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
     * @param  mixed  $value  The value to convert.
     * @return string The converted string.
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

    public function setTarget(string $target): void
    {
        $this->config->setPath($target);
    }

    public function getTarget(): string
    {
        return $this->config->getPath();
    }
}
