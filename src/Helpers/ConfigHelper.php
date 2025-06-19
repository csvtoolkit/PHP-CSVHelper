<?php

namespace CsvToolkit\Helpers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;

/**
 * Helper class for configuration management and conversions.
 */
class ConfigHelper
{
    /**
     * Converts CsvConfig to SplConfig when needed for fallback scenarios.
     */
    public static function convertToSplConfig(CsvConfig|SplConfig $csvConfig): SplConfig
    {
        if ($csvConfig instanceof SplConfig) {
            return $csvConfig;
        }

        return (new SplConfig())
            ->setPath($csvConfig->getPath())
            ->setDelimiter($csvConfig->getDelimiter())
            ->setEnclosure($csvConfig->getEnclosure())
            ->setEscape($csvConfig->getEscape())
            ->setHasHeader($csvConfig->hasHeader())
            ->setOffset($csvConfig->getOffset());
    }

    /**
     * Ensures we have the correct config type, converting if necessary.
     */
    public static function ensureSplConfig(CsvConfig|SplConfig|null $config): SplConfig
    {
        if ($config instanceof SplConfig) {
            return $config;
        }

        if ($config instanceof CsvConfig) {
            return self::convertToSplConfig($config);
        }

        return new SplConfig();
    }

    /**
     * Ensures we have the correct config type for FastCSV.
     */
    public static function ensureFastCsvConfig(CsvConfig|SplConfig|null $config): CsvConfig
    {
        if ($config instanceof CsvConfig) {
            return $config;
        }

        if ($config instanceof SplConfig) {
            // Convert SplConfig to CsvConfig using similar pattern
            return self::convertSplToCsvConfig($config);
        }

        return new CsvConfig();
    }

    /**
     * Converts SplConfig to CsvConfig for upgrade scenarios.
     */
    private static function convertSplToCsvConfig(SplConfig $splConfig): CsvConfig
    {
        return (new CsvConfig())
            ->setPath($splConfig->getPath())
            ->setDelimiter($splConfig->getDelimiter())
            ->setEnclosure($splConfig->getEnclosure())
            ->setEscape($splConfig->getEscape())
            ->setHasHeader($splConfig->hasHeader())
            ->setOffset($splConfig->getOffset());
    }
}
