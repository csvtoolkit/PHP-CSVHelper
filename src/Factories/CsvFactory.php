<?php

namespace CsvToolkit\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Readers\CsvReader;
use CsvToolkit\Readers\SplCsvReader;
use CsvToolkit\Writers\CsvWriter;
use CsvToolkit\Writers\SplCsvWriter;

/**
 * Factory class for creating CSV readers and writers.
 *
 * Automatically selects the best available implementation:
 * - FastCSV extension implementations when available (higher performance)
 * - SplFileObject implementations as fallback (always available)
 */
class CsvFactory
{
    /**
     * Creates a CSV reader using the best available implementation.
     *
     * @param string|null $source Optional CSV file path
     * @param CsvConfigInterface|null $config Optional configuration object
     * @return CsvReaderInterface The reader instance
     */
    public static function createReader(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    ): CsvReaderInterface {
        if (extension_loaded('fastcsv')) {
            return new CsvReader($source, $config);
        }

        return new SplCsvReader($source, $config);
    }

    /**
     * Creates a CSV writer using the best available implementation.
     *
     * @param string|null $target Optional output file path
     * @param CsvConfigInterface|null $config Optional configuration object
     * @param array|null $headers Optional header row
     * @return CsvWriterInterface The writer instance
     */
    public static function createWriter(
        ?string $target = null,
        ?CsvConfigInterface $config = null,
        ?array $headers = null
    ): CsvWriterInterface {
        if (extension_loaded('fastcsv')) {
            return new CsvWriter($target, $config, $headers);
        }

        return new SplCsvWriter($target, $config);
    }

    /**
     * Creates a default CSV configuration.
     *
     * @return CsvConfigInterface The configuration object
     */
    public static function createConfig(): CsvConfigInterface
    {
        return new CsvConfig();
    }

    /**
     * Checks if the FastCSV extension is available.
     *
     * @return bool True if FastCSV extension is loaded, false otherwise
     */
    public static function isFastCsvAvailable(): bool
    {
        return extension_loaded('fastcsv');
    }

    /**
     * Gets information about the current CSV implementation.
     *
     * @return array{implementation: string, extension_loaded: bool, version: string|null}
     */
    public static function getImplementationInfo(): array
    {
        $isFastCsvLoaded = extension_loaded('fastcsv');

        return [
            'implementation' => $isFastCsvLoaded ? 'FastCSV' : 'SplFileObject',
            'extension_loaded' => $isFastCsvLoaded,
            'version' => $isFastCsvLoaded ? (phpversion('fastcsv') ?: null) : null,
        ];
    }
}
