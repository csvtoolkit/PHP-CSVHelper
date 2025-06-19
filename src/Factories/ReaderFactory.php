<?php

namespace CsvToolkit\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Helpers\ConfigHelper;
use CsvToolkit\Helpers\ExtensionHelper;
use CsvToolkit\Readers\CsvReader;
use CsvToolkit\Readers\SplCsvReader;

/**
 * Factory class for creating CSV readers.
 *
 * Automatically selects the best available implementation:
 * - FastCSV extension implementation when available (higher performance)
 * - SplFileObject implementation as fallback (always available)
 */
class ReaderFactory
{
    /**
     * Creates a CSV reader using the best available implementation.
     *
     * @param string|null $source Optional CSV file path
     * @param CsvConfig|SplConfig|null $config Optional configuration object
     * @return CsvReaderInterface The reader instance
     */
    public static function create(
        ?string $source = null,
        CsvConfig|SplConfig|null $config = null
    ): CsvReaderInterface {
        // If user specifically provides SplConfig, use SplCsvReader
        if ($config instanceof SplConfig) {
            return self::createSpl($source, $config);
        }

        // If user provides CsvConfig or null, use best available
        if (ExtensionHelper::isFastCsvLoaded()) {
            $csvConfig = ConfigHelper::ensureFastCsvConfig($config);

            return self::createFastCsv($source, $csvConfig);
        }

        $splConfig = ConfigHelper::ensureSplConfig($config);

        return self::createSpl($source, $splConfig);
    }

    /**
     * Creates a CSV reader using the FastCSV extension.
     *
     * @param string|null $source Optional CSV file path
     * @param CsvConfig|SplConfig|null $config Optional configuration object
     * @return CsvReader The FastCSV reader instance
     * @throws \RuntimeException If FastCSV extension is not available
     */
    public static function createFastCsv(
        ?string $source = null,
        CsvConfig|SplConfig|null $config = null
    ): CsvReader {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            throw new \RuntimeException('FastCSV extension is not available');
        }

        $csvConfig = ConfigHelper::ensureFastCsvConfig($config);
        if ($source !== null) {
            $csvConfig->setPath($source);
        }

        return new CsvReader($source, $csvConfig);
    }

    /**
     * Creates a CSV reader using SplFileObject.
     *
     * @param string|null $source Optional CSV file path
     * @param SplConfig|null $config Optional configuration object
     * @return SplCsvReader The SplFileObject reader instance
     */
    public static function createSpl(
        ?string $source = null,
        ?SplConfig $config = null
    ): SplCsvReader {
        $config ??= ConfigFactory::createSpl($source);

        return new SplCsvReader($source, $config);
    }
}
