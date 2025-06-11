<?php

namespace Tests;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Readers\CsvReader;
use Phpcsv\CsvHelper\Readers\SplCsvReader;
use Phpcsv\CsvHelper\Writers\CsvWriter;
use Phpcsv\CsvHelper\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CompatibilityTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    private array $testData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->generateTestData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestFiles();
        $this->cleanupTestDirectory();
    }

    private function setupTestDirectory(): void
    {
        if (! is_dir(self::TEST_DATA_DIR)) {
            mkdir(self::TEST_DATA_DIR, 0o777, true);
        }
    }

    private function generateTestData(): void
    {
        $this->testData = [
            ['Name', 'Age', 'Email', 'Country'],
            ['John Doe', '30', 'john@example.com', 'USA'],
            ['Jane Smith', '25', 'jane@example.com', 'UK'],
            ['Bob Johnson', '35', 'bob@example.com', 'Canada'],
            ['Alice Brown', '28', 'alice@example.com', 'Australia'],
            ['José García', '32', 'jose@example.com', 'Spain'],
        ];
    }

    private function cleanupTestFiles(): void
    {
        $files = glob(self::TEST_DATA_DIR . '/*.csv');
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function cleanupTestDirectory(): void
    {
        if (is_dir(self::TEST_DATA_DIR)) {
            rmdir(self::TEST_DATA_DIR);
        }
    }

    #[Test]
    public function test_reader_constructor_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/constructor_test.csv';
        $this->createTestFile($testFile, $this->testData);

        // Test null constructor
        $splReader = new SplCsvReader();
        $this->assertEquals('', $splReader->getSource());
        $this->assertEquals(-1, $splReader->getCurrentPosition());

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader();
            $this->assertEquals('', $csvReader->getSource());
            $this->assertEquals(-1, $csvReader->getCurrentPosition());
        }

        // Test constructor with file
        $splReader = new SplCsvReader($testFile);
        $this->assertEquals($testFile, $splReader->getSource());

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $this->assertEquals($testFile, $csvReader->getSource());
        }
    }

    #[Test]
    public function test_writer_constructor_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/writer_test.csv';

        // Test null constructor
        $splWriter = new SplCsvWriter();
        $this->assertEquals('', $splWriter->getSource());

        if (extension_loaded('fastcsv')) {
            $csvWriter = new CsvWriter();
            $this->assertEquals('', $csvWriter->getSource());
        }

        // Test constructor with file
        $splWriter = new SplCsvWriter($testFile);
        $this->assertEquals($testFile, $splWriter->getSource());

        if (extension_loaded('fastcsv')) {
            $csvWriter = new CsvWriter($testFile);
            $this->assertEquals($testFile, $csvWriter->getSource());
        }
    }

    #[Test]
    public function test_reader_record_count_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/count_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $splCount = $splReader->getRecordCount();

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $csvCount = $csvReader->getRecordCount();

            $this->assertEquals($splCount, $csvCount, 'Record counts should be identical');
        }

        // Should be 5 (excluding header)
        $this->assertEquals(5, $splCount);
    }

    #[Test]
    public function test_reader_header_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/header_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $splHeader = $splReader->getHeader();

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $csvHeader = $csvReader->getHeader();

            $this->assertEquals($splHeader, $csvHeader, 'Headers should be identical');
        }

        $this->assertEquals(['Name', 'Age', 'Email', 'Country'], $splHeader);
    }

    #[Test]
    public function test_reader_sequential_reading_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/sequential_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $splRecords = [];

        while (($record = $splReader->nextRecord()) !== false) {
            $splRecords[] = $record;
        }

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $csvRecords = [];

            while (($record = $csvReader->nextRecord()) !== false) {
                $csvRecords[] = $record;
            }

            $this->assertEquals($splRecords, $csvRecords, 'Sequential reading should produce identical results');
        }

        // Verify we got all data records (excluding header)
        $expectedRecords = array_slice($this->testData, 1);
        $this->assertEquals($expectedRecords, $splRecords);
    }

    #[Test]
    public function test_reader_position_tracking_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/position_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $csvReader = null;

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
        }

        // Initial position should be -1
        $this->assertEquals(-1, $splReader->getCurrentPosition());
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $this->assertEquals(-1, $csvReader->getCurrentPosition());
        }

        // After first read, position should be 0
        $splReader->nextRecord();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvReader->nextRecord();
            $this->assertEquals($splReader->getCurrentPosition(), $csvReader->getCurrentPosition());
        }
        $this->assertEquals(0, $splReader->getCurrentPosition());

        // After second read, position should be 1
        $splReader->nextRecord();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvReader->nextRecord();
            $this->assertEquals($splReader->getCurrentPosition(), $csvReader->getCurrentPosition());
        }
        $this->assertEquals(1, $splReader->getCurrentPosition());

        // After rewind, position should be -1
        $splReader->rewind();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvReader->rewind();
            $this->assertEquals($splReader->getCurrentPosition(), $csvReader->getCurrentPosition());
        }
        $this->assertEquals(-1, $splReader->getCurrentPosition());
    }

    #[Test]
    public function test_reader_seek_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/seek_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $csvReader = null;

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
        }

        // Seek to position 2
        $splRecord = $splReader->seek(2);
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvRecord = $csvReader->seek(2);
            $this->assertEquals($splRecord, $csvRecord, 'Seek results should be identical');
            $this->assertEquals($splReader->getCurrentPosition(), $csvReader->getCurrentPosition());
        }

        $this->assertEquals($this->testData[3], $splRecord); // 0-based data records
        $this->assertEquals(2, $splReader->getCurrentPosition());

        // Seek beyond bounds should return false
        $splResult = $splReader->seek(100);
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvResult = $csvReader->seek(100);
            $this->assertEquals($splResult, $csvResult, 'Out of bounds seek should behave identically');
        }
        $this->assertFalse($splResult);
    }

    #[Test]
    public function test_reader_has_methods_compatibility(): void
    {
        $testFile = self::TEST_DATA_DIR . '/has_methods_test.csv';
        $this->createTestFile($testFile, $this->testData);

        $splReader = new SplCsvReader($testFile);
        $csvReader = null;

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
        }

        // hasRecords should be identical
        $splHasRecords = $splReader->hasRecords();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvHasRecords = $csvReader->hasRecords();
            $this->assertEquals($splHasRecords, $csvHasRecords, 'hasRecords should be identical');
        }
        $this->assertTrue($splHasRecords);

        // hasNext should be identical initially
        $splHasNext = $splReader->hasNext();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvHasNext = $csvReader->hasNext();
            $this->assertEquals($splHasNext, $csvHasNext, 'hasNext should be identical initially');
        }
        $this->assertTrue($splHasNext);

        // Read all records and check hasNext at the end
        while ($splReader->nextRecord() !== false) {
            if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
                $csvReader->nextRecord();
            }
        }

        $splHasNext = $splReader->hasNext();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvHasNext = $csvReader->hasNext();
            $this->assertEquals($splHasNext, $csvHasNext, 'hasNext should be identical after reading all');
        }
        $this->assertFalse($splHasNext);
    }

    #[Test]
    #[DataProvider('csvConfigProvider')]
    public function test_reader_config_compatibility(CsvConfig $config): void
    {
        $testFile = self::TEST_DATA_DIR . '/config_test.csv';

        // Create file with specific config
        $writer = new SplCsvWriter($testFile, $config);
        foreach ($this->testData as $record) {
            $writer->write($record);
        }
        unset($writer);

        $splReader = new SplCsvReader($testFile, $config);
        $csvReader = null;

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile, $config);
        }

        // Config should be identical
        $this->assertEquals($config->getDelimiter(), $splReader->getConfig()->getDelimiter());
        $this->assertEquals($config->getEnclosure(), $splReader->getConfig()->getEnclosure());
        $this->assertEquals($config->hasHeader(), $splReader->getConfig()->hasHeader());

        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $this->assertEquals($config->getDelimiter(), $csvReader->getConfig()->getDelimiter());
            $this->assertEquals($config->getEnclosure(), $csvReader->getConfig()->getEnclosure());
            $this->assertEquals($config->hasHeader(), $csvReader->getConfig()->hasHeader());
        }

        // Reading should produce identical results
        $splRecord = $splReader->nextRecord();
        if ($csvReader instanceof \Phpcsv\CsvHelper\Readers\CsvReader) {
            $csvRecord = $csvReader->nextRecord();
            $this->assertEquals($splRecord, $csvRecord, 'Config-based reading should produce identical results');
        }
    }

    #[Test]
    public function test_writer_basic_writing_compatibility(): void
    {
        $splFile = self::TEST_DATA_DIR . '/spl_writer_test.csv';
        $csvFile = self::TEST_DATA_DIR . '/csv_writer_test.csv';

        $splWriter = new SplCsvWriter($splFile);
        $csvWriter = null;
        $hasFastCSV = extension_loaded('fastcsv');

        if ($hasFastCSV) {
            $csvWriter = new CsvWriter($csvFile);
        }

        // Write same data to both
        foreach ($this->testData as $record) {
            $splWriter->write($record);
            if ($csvWriter instanceof \Phpcsv\CsvHelper\Writers\CsvWriter) {
                $csvWriter->write($record);
            }
        }

        unset($splWriter);
        if ($csvWriter instanceof \Phpcsv\CsvHelper\Writers\CsvWriter) {
            unset($csvWriter);
        }

        // Files should have identical content (or at least equivalent CSV structure)
        $splContent = file_get_contents($splFile);
        if ($hasFastCSV) {
            $csvContent = file_get_contents($csvFile);

            // Parse both files to compare data (not necessarily exact string match due to different implementations)
            $splLines = str_getcsv($splContent, "\n");
            $csvLines = str_getcsv($csvContent, "\n");

            $this->assertCount(count($csvLines), $splLines, 'Both files should have same number of lines');
        }

        // Verify SPL file has correct content
        $lines = explode("\n", trim($splContent));
        $this->assertCount(6, $lines); // 6 records
        $this->assertStringContainsString('Name,Age,Email,Country', $lines[0]);
    }

    #[Test]
    public function test_writer_writeall_compatibility(): void
    {
        $splFile = self::TEST_DATA_DIR . '/spl_writeall_test.csv';
        $csvFile = self::TEST_DATA_DIR . '/csv_writeall_test.csv';

        $splWriter = new SplCsvWriter($splFile);
        $csvWriter = null;
        $hasFastCSV = extension_loaded('fastcsv');

        if ($hasFastCSV) {
            $csvWriter = new CsvWriter($csvFile);
        }

        // Write all data at once
        $splWriter->writeAll($this->testData);
        if ($csvWriter instanceof \Phpcsv\CsvHelper\Writers\CsvWriter) {
            $csvWriter->writeAll($this->testData);
        }

        unset($splWriter);
        if ($csvWriter instanceof \Phpcsv\CsvHelper\Writers\CsvWriter) {
            unset($csvWriter);
        }

        // Both files should exist and have content
        $this->assertFileExists($splFile);
        if ($hasFastCSV) {
            $this->assertFileExists($csvFile);
        }

        $splContent = file_get_contents($splFile);
        $splLines = explode("\n", trim($splContent));
        $this->assertCount(6, $splLines);

        if ($hasFastCSV) {
            $csvContent = file_get_contents($csvFile);
            $csvLines = explode("\n", trim($csvContent));
            $this->assertCount(count($splLines), $csvLines, 'WriteAll should produce same number of lines');
        }
    }

    #[Test]
    public function test_round_trip_compatibility(): void
    {
        // Test writing with one implementation and reading with another
        $testFile = self::TEST_DATA_DIR . '/round_trip_test.csv';

        // Write with SplCsvWriter
        $writer = new SplCsvWriter($testFile);
        foreach ($this->testData as $record) {
            $writer->write($record);
        }
        unset($writer);

        // Read with both implementations
        $splReader = new SplCsvReader($testFile);
        $splRecords = [];
        while (($record = $splReader->nextRecord()) !== false) {
            $splRecords[] = $record;
        }

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $csvRecords = [];
            while (($record = $csvReader->nextRecord()) !== false) {
                $csvRecords[] = $record;
            }

            $this->assertEquals($splRecords, $csvRecords, 'Round trip should produce identical results');
        }

        // Verify data integrity
        $expectedRecords = array_slice($this->testData, 1); // Exclude header
        $this->assertEquals($expectedRecords, $splRecords);
    }

    public static function csvConfigProvider(): array
    {
        return [
            'semicolon_delimiter' => [(new CsvConfig())->setDelimiter(';')],
            'custom_enclosure' => [(new CsvConfig())->setEnclosure("'")],
            'tab_delimiter' => [(new CsvConfig())->setDelimiter("\t")],
            'no_headers' => [(new CsvConfig())->setHasHeader(false)],
            'complex_config' => [
                (new CsvConfig())
                    ->setDelimiter('|')
                    ->setEnclosure('`')
                    ->setEscape('/')
                    ->setHasHeader(false),
            ],
        ];
    }

    private function createTestFile(string $filePath, array $data): void
    {
        $writer = new SplCsvWriter($filePath);
        foreach ($data as $record) {
            $writer->write($record);
        }
        unset($writer);
    }

    #[Test]
    public function test_empty_file_compatibility(): void
    {
        $emptyFile = self::TEST_DATA_DIR . '/empty.csv';
        file_put_contents($emptyFile, '');

        $splReader = new SplCsvReader($emptyFile);

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($emptyFile);

            // Both should throw exception or handle empty file identically
            try {
                $splCount = $splReader->getRecordCount();

                try {
                    $csvCount = $csvReader->getRecordCount();
                    $this->assertEquals($splCount, $csvCount, 'Both readers should return the same count for empty files');
                } catch (\Exception $csvException) {
                    $this->fail('SplCsvReader succeeded but CsvReader threw exception: ' . $csvException->getMessage());
                }
            } catch (\Exception $splException) {
                // If SplCsvReader throws, CsvReader should throw the same type
                try {
                    $csvReader->getRecordCount();
                    $this->fail('SplCsvReader threw exception but CsvReader succeeded');
                } catch (\Exception $csvException) {
                    $this->assertEquals(
                        $splException::class,
                        $csvException::class,
                        'Both readers should throw the same type of exception for empty files'
                    );
                }
            }
        } else {
            // If FastCSV is not loaded, just ensure SplCsvReader handles empty files gracefully
            try {
                $count = $splReader->getRecordCount();
                $this->assertIsInt($count, 'SplCsvReader should return an integer count for empty files');
            } catch (\Exception $e) {
                $this->assertInstanceOf(
                    \Phpcsv\CsvHelper\Exceptions\EmptyFileException::class,
                    $e,
                    'SplCsvReader should throw EmptyFileException for empty files'
                );
            }
        }
    }

    #[Test]
    public function test_unicode_compatibility(): void
    {
        $unicodeData = [
            ['Name', 'City', 'Description'],
            ['José', 'Madrid', 'España'],
            ['François', 'Paris', 'Café owner'],
            ['北京', 'China', 'Capital city'],
        ];

        $testFile = self::TEST_DATA_DIR . '/unicode_test.csv';

        // Write with SplCsvWriter
        $writer = new SplCsvWriter($testFile);
        foreach ($unicodeData as $record) {
            $writer->write($record);
        }
        unset($writer);

        // Read with both implementations
        $splReader = new SplCsvReader($testFile);
        $splRecords = [];
        while (($record = $splReader->nextRecord()) !== false) {
            $splRecords[] = $record;
        }

        if (extension_loaded('fastcsv')) {
            $csvReader = new CsvReader($testFile);
            $csvRecords = [];
            while (($record = $csvReader->nextRecord()) !== false) {
                $csvRecords[] = $record;
            }

            $this->assertEquals($splRecords, $csvRecords, 'Unicode handling should be identical');
        }

        // Verify Unicode data is preserved
        $expectedRecords = array_slice($unicodeData, 1);
        $this->assertEquals($expectedRecords, $splRecords);
    }
}
