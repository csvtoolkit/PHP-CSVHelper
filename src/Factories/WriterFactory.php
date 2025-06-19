<?php

namespace CsvToolkit\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Helpers\ConfigHelper;
use CsvToolkit\Helpers\ExtensionHelper;
use CsvToolkit\Writers\CsvWriter;
use CsvToolkit\Writers\SplCsvWriter;

/**
 * Factory for creating CSV writers
 *
 * This factory automatically selects the best available implementation
 * (FastCSV extension if available, SplFileObject as fallback).
 */
class WriterFactory
{
    /**
     * Creates a CSV writer with automatic implementation selection
     *
     * @param string $destination File path to write to
     * @param CsvConfig|SplConfig|null $config Configuration object
     */
    public static function create(
        string $destination,
        CsvConfig|SplConfig|null $config = null
    ): CsvWriterInterface {

        // If user specifically provides SplConfig, use SplCsvWriter
        if ($config instanceof SplConfig) {
            return self::createSpl($destination, $config);
        }

        // If user provides CsvConfig or null, use best available
        if (ExtensionHelper::isFastCsvLoaded()) {
            $csvConfig = ConfigHelper::ensureFastCsvConfig($config);

            return self::createFastCsv($destination, $csvConfig);
        }

        $splConfig = ConfigHelper::ensureSplConfig($config);

        return self::createSpl($destination, $splConfig);
    }

    /**
     * Creates a FastCSV-based writer
     *
     * @param string $destination File path to write to
     * @param CsvConfig|SplConfig|null $config FastCSV configuration
     * @throws \RuntimeException If FastCSV extension is not available
     */
    public static function createFastCsv(
        string $destination,
        CsvConfig|SplConfig|null $config = null
    ): CsvWriter {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            throw new \RuntimeException('FastCSV extension is not available');
        }

        $csvConfig = ConfigHelper::ensureFastCsvConfig($config);
        $csvConfig->setPath($destination);

        return new CsvWriter($destination, $csvConfig);
    }

    /**
     * Creates an SplFileObject-based writer
     *
     * @param string $destination File path to write to
     * @param SplConfig|null $config SplFileObject configuration
     */
    public static function createSpl(string $destination, ?SplConfig $config = null): SplCsvWriter
    {
        $config ??= ConfigFactory::createSpl($destination);
        $config->setPath($destination);

        return new SplCsvWriter($destination, $config);
    }
}
