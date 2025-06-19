<?php

namespace CsvToolkit\Helpers;

use CsvToolkit\Exceptions\CsvWriterException;
use CsvToolkit\Exceptions\DirectoryNotFoundException;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;

/**
 * Helper class for file validation operations.
 * Provides reusable validation methods for file operations.
 */
class FileValidator
{
    /**
     * Validates that a file exists and is readable.
     *
     * @param string $filePath The file path to validate
     * @throws FileNotFoundException If file doesn't exist
     * @throws FileNotReadableException If file cannot be read
     * @throws EmptyFileException If file is empty
     */
    public static function validateFileReadable(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        if (is_dir($filePath)) {
            throw new EmptyFileException($filePath);
        }

        if (! is_readable($filePath)) {
            throw new FileNotReadableException($filePath);
        }

        if (filesize($filePath) === 0) {
            throw new EmptyFileException($filePath);
        }
    }

    /**
     * Validates that a file path is writable (directory exists and is writable).
     *
     * @param string $filePath The file path to validate
     * @throws CsvWriterException If file path is empty
     * @throws DirectoryNotFoundException If directory doesn't exist
     * @throws CsvWriterException If directory is not writable
     */
    public static function validateFileWritable(string $filePath): void
    {
        if (in_array(trim($filePath), ['', '0'], true)) {
            throw new CsvWriterException('Target file path is required');
        }

        $directory = dirname($filePath);

        if (! is_dir($directory)) {
            throw new DirectoryNotFoundException($directory);
        }

        if (! is_writable($directory)) {
            throw new CsvWriterException("Directory is not writable: {$directory}");
        }
    }

    /**
     * Validates that a file exists, is readable, and is not empty.
     * More permissive version that doesn't throw on empty files.
     *
     * @param string $filePath The file path to validate
     * @throws FileNotFoundException If file doesn't exist
     * @throws FileNotReadableException If file cannot be read
     */
    public static function validateFileExists(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        if (! is_readable($filePath)) {
            throw new FileNotReadableException($filePath);
        }
    }
}
