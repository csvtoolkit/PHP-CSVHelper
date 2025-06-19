<?php

namespace CsvToolkit\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Helpers\ExtensionHelper;

/**
 * Factory class for creating CSV configurations.
 *
 * Automatically returns the appropriate configuration based on available extensions:
 * - CsvConfig when FastCSV extension is available (full features)
 * - SplConfig when only SplFileObject is available (basic features)
 */
class ConfigFactory
{
    /**
     * Creates the best available CSV configuration.
     *
     * @param string|null $path Optional file path to set initially
     * @param bool $hasHeader Whether the CSV file has a header row (default: true)
     * @return CsvConfig|SplConfig The appropriate configuration object
     */
    public static function create(?string $path = null, bool $hasHeader = true): CsvConfig|SplConfig
    {
        if (ExtensionHelper::isFastCsvLoaded()) {
            return self::createFastCsv($path, $hasHeader);
        }

        return self::createSpl($path, $hasHeader);
    }

    /**
     * Creates a FastCSV configuration (requires extension).
     *
     * @param string|null $path Optional file path to set initially
     * @param bool $hasHeader Whether the CSV file has a header row (default: true)
     * @return CsvConfig The FastCSV configuration object
     * @throws \RuntimeException If FastCSV extension is not available
     */
    public static function createFastCsv(?string $path = null, bool $hasHeader = true): CsvConfig
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            throw new \RuntimeException('FastCSV extension is not available');
        }

        return new CsvConfig($path, $hasHeader);
    }

    /**
     * Creates an SplFileObject configuration (always available).
     *
     * @param string|null $path Optional file path to set initially
     * @param bool $hasHeader Whether the CSV file has a header row (default: true)
     * @return SplConfig The SplFileObject configuration object
     */
    public static function createSpl(?string $path = null, bool $hasHeader = true): SplConfig
    {
        return new SplConfig($path, $hasHeader);
    }
}
