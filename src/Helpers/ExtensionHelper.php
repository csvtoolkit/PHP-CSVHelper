<?php

namespace CsvToolkit\Helpers;

/**
 * Helper class for extension-related utilities.
 *
 * Provides common functions for checking extension availability and capabilities.
 */
class ExtensionHelper
{
    /**
     * Checks if the FastCSV extension is loaded.
     *
     * @return bool True if FastCSV extension is available, false otherwise
     */
    public static function isFastCsvLoaded(): bool
    {
        return extension_loaded('fastcsv');
    }

    /**
     * Alias for isFastCsvLoaded() for backward compatibility.
     *
     * @return bool True if FastCSV extension is available, false otherwise
     */
    public static function isFastCsvAvailable(): bool
    {
        return self::isFastCsvLoaded();
    }

    /**
     * Gets the FastCSV extension version.
     *
     * @return string|null The extension version or null if not available
     */
    public static function getFastCsvVersion(): ?string
    {
        if (! self::isFastCsvLoaded()) {
            return null;
        }

        return phpversion('fastcsv') ?: null;
    }

    /**
     * Gets information about the FastCSV extension.
     *
     * @return array{loaded: bool, version: string|null, available_classes: array<string>}
     */
    public static function getFastCsvInfo(): array
    {
        $loaded = self::isFastCsvLoaded();

        return [
            'loaded' => $loaded,
            'version' => $loaded ? self::getFastCsvVersion() : null,
            'available_classes' => $loaded ? [
                'FastCSVConfig',
                'FastCSVReader',
                'FastCSVWriter',
            ] : [],
        ];
    }

    /**
     * Determines the best available CSV implementation.
     *
     * @return string Either 'fastcsv' or 'spl'
     */
    public static function getBestImplementation(): string
    {
        return self::isFastCsvLoaded() ? 'fastcsv' : 'spl';
    }

    /**
     * Gets the preferred CSV extension.
     *
     * @return string The preferred extension name
     */
    public static function getPreferredExtension(): string
    {
        return self::getBestImplementation();
    }

    /**
     * Gets all available CSV extensions.
     *
     * @return array<string> Array of available extension names
     */
    public static function getAvailableExtensions(): array
    {
        $extensions = ['spl']; // SplFileObject is always available

        if (self::isFastCsvLoaded()) {
            array_unshift($extensions, 'fastcsv'); // FastCSV preferred
        }

        return $extensions;
    }

    /**
     * Gets detailed information about all CSV extensions.
     *
     * @return array<string, array{available: bool, description: string}> Extension information
     */
    public static function getExtensionInfo(): array
    {
        return [
            'fastcsv' => [
                'available' => self::isFastCsvLoaded(),
                'description' => 'FastCSV - High-performance CSV extension with advanced features',
            ],
            'spl' => [
                'available' => true, // Always available
                'description' => 'SPL - Standard PHP Library SplFileObject for basic CSV operations',
            ],
        ];
    }

    /**
     * Checks if a specific class exists (useful for extension classes).
     *
     * @param string $className The class name to check
     * @return bool True if class exists, false otherwise
     */
    public static function classExists(string $className): bool
    {
        return class_exists($className);
    }
}
