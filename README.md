# CSV Toolkit

[![Latest Stable Version](https://poser.pugx.org/csvtoolkit/csv-helper/v/stable)](https://packagist.org/packages/csvtoolkit/csv-helper)
[![Total Downloads](https://poser.pugx.org/csvtoolkit/csv-helper/downloads)](https://packagist.org/packages/csvtoolkit/csv-helper)
[![Latest Unstable Version](https://poser.pugx.org/csvtoolkit/csv-helper/v/unstable)](https://packagist.org/packages/csvtoolkit/csv-helper)
[![License](https://poser.pugx.org/csvtoolkit/csv-helper/license)](https://packagist.org/packages/csvtoolkit/csv-helper)
[![PHP Version Require](https://poser.pugx.org/csvtoolkit/csv-helper/require/php)](https://packagist.org/packages/csvtoolkit/csv-helper)

A modern, high-performance CSV processing library for PHP that automatically selects the best available implementation. CsvToolkit provides a unified interface for reading and writing CSV files, with automatic fallback between the FastCSV extension (when available) and PHP's built-in SplFileObject.

## Features

- **Automatic Implementation Selection**: Automatically uses FastCSV extension when available, falls back to SplFileObject
- **Unified Interface**: Same API regardless of underlying implementation
- **High Performance**: FastCSV extension provides significant performance improvements
- **Flexible Configuration**: Support for custom delimiters, enclosures, escape characters, and headers
- **Factory Pattern**: Simple factory methods for creating readers and writers
- **Action Classes**: Convenient action classes for quick CSV operations
- **Comprehensive Testing**: Full test suite with 146+ tests covering both implementations
- **Type Safety**: Full type declarations and PHPDoc documentation

## Installation

Install via Composer:

```bash
composer require csvtoolkit/csv-helper
```

### Optional: FastCSV Extension

For enhanced performance, install the FastCSV PHP extension:

```bash
# The library will automatically detect and use it when available
# Falls back to SplFileObject when not available
```

## Quick Start

### Using Factory (Recommended)

```php
use CsvToolkit\Factories\CsvFactory;

// Create reader (automatically selects best implementation)
$reader = CsvFactory::createReader('data.csv');

// Read all records
while (($record = $reader->nextRecord()) !== false) {
    print_r($record);
}

// Create writer
$writer = CsvFactory::createWriter('output.csv');
$writer->write(['Name', 'Age', 'Email']); // Header
$writer->write(['John Doe', '30', 'john@example.com']);
```

### Using Action Classes

```php
use CsvToolkit\Actions\CsvReaderAction;
use CsvToolkit\Actions\CsvWriterAction;

// Quick reader creation
$reader = CsvReaderAction::create('data.csv');

// Quick writer creation  
$writer = CsvWriterAction::create('output.csv');
```

### Manual Implementation Selection

```php
use CsvToolkit\Readers\CsvReader;      // FastCSV implementation
use CsvToolkit\Readers\SplCsvReader;   // SplFileObject implementation
use CsvToolkit\Writers\CsvWriter;      // FastCSV implementation
use CsvToolkit\Writers\SplCsvWriter;   // SplFileObject implementation
use CsvToolkit\Configs\CsvConfig;

// Configure CSV settings
$config = new CsvConfig();
$config->setDelimiter(';')
       ->setEnclosure('"')
       ->setHasHeader(true);

// Use specific implementation
$reader = new SplCsvReader('data.csv', $config);
```

## Configuration

```php
use CsvToolkit\Configs\CsvConfig;

$config = new CsvConfig();
$config->setDelimiter(',')           // Field delimiter
       ->setEnclosure('"')           // Field enclosure
       ->setEscape('\\')             // Escape character
       ->setHasHeader(true);         // First row is header
```

## Reading CSV Files

```php
use CsvToolkit\Factories\CsvFactory;

$reader = CsvFactory::createReader('data.csv');

// Get header (if configured)
$header = $reader->getHeader();

// Read records sequentially
while (($record = $reader->nextRecord()) !== false) {
    // Process record
}

// Or seek to specific position
$record = $reader->seek(5); // Get 6th record (0-based)

// Check if more records available
if ($reader->hasNext()) {
    $nextRecord = $reader->nextRecord();
}

// Get total record count
$totalRecords = $reader->getRecordCount();
```

## Writing CSV Files

```php
use CsvToolkit\Factories\CsvFactory;

$writer = CsvFactory::createWriter('output.csv');

// Write single record
$writer->write(['John Doe', '30', 'john@example.com']);

// Write multiple records at once
$records = [
    ['Name', 'Age', 'Email'],
    ['John Doe', '30', 'john@example.com'],
    ['Jane Smith', '25', 'jane@example.com']
];
$writer->writeAll($records);
```

## Implementation Detection

```php
use CsvToolkit\Factories\CsvFactory;

// Check if FastCSV is available
if (CsvFactory::isFastCsvAvailable()) {
    echo "Using high-performance FastCSV extension";
} else {
    echo "Using SplFileObject fallback";
}

// Get detailed implementation info
$info = CsvFactory::getImplementationInfo();
print_r($info);
// Array
// (
//     [implementation] => FastCSV
//     [extension_loaded] => 1
//     [version] => 0.0.1
// )
```

## Exception Handling

The library provides specific exceptions for different error conditions:

```php
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\DirectoryNotFoundException;
use CsvToolkit\Exceptions\CsvReaderException;
use CsvToolkit\Exceptions\CsvWriterException;
use CsvToolkit\Exceptions\EmptyFileException;

try {
    $reader = CsvFactory::createReader('nonexistent.csv');
} catch (FileNotFoundException $e) {
    echo "File not found: " . $e->getMessage();
} catch (CsvReaderException $e) {
    echo "Reader error: " . $e->getMessage();
}
```

## Performance

When the FastCSV extension is available, CsvToolkit provides significant performance improvements:

- **Reading**: Up to 3-5x faster than SplFileObject
- **Writing**: Up to 2-3x faster than SplFileObject  
- **Memory Usage**: Lower memory footprint for large files

The library automatically selects the best available implementation without any code changes required.

## Testing

Run the test suite:

```bash
# Test with SplFileObject (fallback)
./vendor/bin/phpunit

# Test with FastCSV extension (if available)
php -d extension=path/to/fastcsv.so ./vendor/bin/phpunit
```

## Contributing

We welcome contributions! Please follow these guidelines:

- Respect all modern PHP coding standards and best practices
- Ensure that all methods include type declarations
- Pass all unit tests before committing your changes
- Tests are required for any new features or bug fixes
- Maintain compatibility with both FastCSV and SplFileObject implementations

## Roadmap

### Current Features ✅
- FastCSV extension integration with automatic fallback
- Factory pattern for implementation selection
- Action classes for convenient usage
- Comprehensive exception handling
- Full type safety and documentation

### Future Features 🚧
- **In Progress**: Enhanced FastCSV extension features
- **Planned**:
  - Data mappers and transformers
  - Built-in validators
  - QCSV (Quoted CSV) support
  - Streaming support for very large files
  - CSV schema validation

## Requirements

- PHP 8.1 or higher
- Optional: FastCSV extension for enhanced performance

## License

This project is open source and available under the MIT License. See the [LICENSE](LICENSE) file for more information.
