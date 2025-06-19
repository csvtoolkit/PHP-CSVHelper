<?php

namespace CsvToolkit\Enums;

/**
 * Character encoding enumeration for CSV files.
 *
 * Defines the supported character encodings for the FastCSV extension.
 */
enum Encoding: int
{
    case UTF8 = 0;
    case UTF16LE = 1;
    case UTF16BE = 2;
    case UTF32LE = 3;
    case UTF32BE = 4;
    case ASCII = 5;
    case LATIN1 = 6;

    /**
     * Get the human-readable name of the encoding.
     */
    public function getName(): string
    {
        return match ($this) {
            self::UTF8 => 'UTF-8',
            self::UTF16LE => 'UTF-16 Little Endian',
            self::UTF16BE => 'UTF-16 Big Endian',
            self::UTF32LE => 'UTF-32 Little Endian',
            self::UTF32BE => 'UTF-32 Big Endian',
            self::ASCII => 'ASCII',
            self::LATIN1 => 'Latin-1 (ISO-8859-1)',
        };
    }

    /**
     * Get the standard encoding identifier.
     */
    public function getIdentifier(): string
    {
        return match ($this) {
            self::UTF8 => 'UTF-8',
            self::UTF16LE => 'UTF-16LE',
            self::UTF16BE => 'UTF-16BE',
            self::UTF32LE => 'UTF-32LE',
            self::UTF32BE => 'UTF-32BE',
            self::ASCII => 'ASCII',
            self::LATIN1 => 'ISO-8859-1',
        };
    }

    /**
     * Check if the encoding supports Byte Order Mark (BOM).
     */
    public function supportsBom(): bool
    {
        return match ($this) {
            self::UTF8, self::UTF16LE, self::UTF16BE, self::UTF32LE, self::UTF32BE => true,
            self::ASCII, self::LATIN1 => false,
        };
    }

    /**
     * Get all available encodings.
     *
     * @return array<string, Encoding>
     */
    public static function all(): array
    {
        return [
            'utf8' => self::UTF8,
            'utf16le' => self::UTF16LE,
            'utf16be' => self::UTF16BE,
            'utf32le' => self::UTF32LE,
            'utf32be' => self::UTF32BE,
            'ascii' => self::ASCII,
            'latin1' => self::LATIN1,
        ];
    }

    /**
     * Create encoding from string identifier.
     */
    public static function fromString(string $identifier): ?Encoding
    {
        $normalized = strtolower(str_replace(['-', '_'], '', $identifier));

        return match ($normalized) {
            'utf8' => self::UTF8,
            'utf16le' => self::UTF16LE,
            'utf16be' => self::UTF16BE,
            'utf32le' => self::UTF32LE,
            'utf32be' => self::UTF32BE,
            'ascii' => self::ASCII,
            'latin1', 'iso88591' => self::LATIN1,
            default => null,
        };
    }
}
