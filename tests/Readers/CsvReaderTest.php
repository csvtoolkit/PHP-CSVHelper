<?php

namespace Tests\Readers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Readers\CsvReader;
use CsvToolkit\Writers\SplCsvWriter;
use FastCSVReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvReader::class)]
class CsvReaderTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';
    private const string SAMPLE_CSV = self::TEST_DATA_DIR . '/fastcsv_sample.csv';

    private array $testData = [];

    private string $testFile;

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
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
            ['Bob Johnson', '35', 'bob@example.com'],
            ['Alice Brown', '28', 'alice@example.com'],
        ];

        $this->testFile = $this->createTestFile($this->testData);
    }

    private function createTestFile(array $data): string
    {
        $filePath = self::SAMPLE_CSV;
        $writer = new SplCsvWriter($filePath);

        foreach ($data as $record) {
            $writer->write($record);
        }

        return $filePath;
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
    public function test_constructor_with_null_parameters(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader();

        $this->assertInstanceOf(CsvReader::class, $reader);
        $this->assertInstanceOf(CsvConfigInterface::class, $reader->getConfig());
        $this->assertEquals('', $reader->getSource());
        $this->assertEquals(-1, $reader->getCurrentPosition());
    }

    #[Test]
    public function test_constructor_with_source_only(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        $this->assertEquals($this->testFile, $reader->getSource());
        $this->assertInstanceOf(CsvConfigInterface::class, $reader->getConfig());
    }

    #[Test]
    public function test_constructor_with_custom_config(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $reader = new CsvReader($this->testFile, $config);

        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertEquals("'", $reader->getConfig()->getEnclosure());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_get_reader_returns_fastcsv_instance(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);
        $fastCsvReader = $reader->getReader();

        $this->assertInstanceOf(FastCSVReader::class, $fastCsvReader);
    }

    #[Test]
    public function test_get_reader_with_nonexistent_file_throws_exception(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $this->expectException(FileNotFoundException::class);

        $reader = new CsvReader('/nonexistent/file.csv');
        $reader->getReader();
    }

    #[Test]
    public function test_get_record_count(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);
        $count = $reader->getRecordCount();

        // Should return 4 (excluding header)
        $this->assertEquals(4, $count);
    }

    #[Test]
    public function test_get_record_count_without_headers(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $config = new CsvConfig();
        $config->setHasHeader(false);

        $reader = new CsvReader($this->testFile, $config);
        $count = $reader->getRecordCount();

        // Should return 5 (all rows)
        $this->assertEquals(5, $count);
    }

    #[Test]
    public function test_get_header(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);
        $header = $reader->getHeader();

        $this->assertEquals(['Name', 'Age', 'Email'], $header);
    }

    #[Test]
    public function test_get_header_disabled_returns_false(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $config = new CsvConfig();
        $config->setHasHeader(false);

        $reader = new CsvReader($this->testFile, $config);
        $header = $reader->getHeader();

        $this->assertFalse($header);
    }

    #[Test]
    public function test_current_position_initial_state(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        $this->assertEquals(-1, $reader->getCurrentPosition());
    }

    #[Test]
    public function test_get_record_without_reading_returns_false(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);
        $record = $reader->getRecord();

        $this->assertFalse($record);
    }

    #[Test]
    public function test_next_record_sequential_reading(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        // Read first record
        $record1 = $reader->nextRecord();
        $this->assertEquals($this->testData[1], $record1);
        $this->assertEquals(0, $reader->getCurrentPosition());

        // Read second record
        $record2 = $reader->nextRecord();
        $this->assertEquals($this->testData[2], $record2);
        $this->assertEquals(1, $reader->getCurrentPosition());

        // getRecord should return cached record
        $cachedRecord = $reader->getRecord();
        $this->assertEquals($record2, $cachedRecord);
    }

    #[Test]
    public function test_seek_to_specific_position(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        // Seek to position 2
        $record = $reader->seek(2);
        $this->assertEquals($this->testData[3], $record);
        $this->assertEquals(2, $reader->getCurrentPosition());

        // getRecord should return the same record
        $cachedRecord = $reader->getRecord();
        $this->assertEquals($record, $cachedRecord);
    }

    #[Test]
    public function test_seek_beyond_bounds_returns_false(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        // Seek beyond file
        $result = $reader->seek(100);
        $this->assertFalse($result);

        // Seek to negative position
        $result = $reader->seek(-1);
        $this->assertFalse($result);
    }

    #[Test]
    public function test_rewind_functionality(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        // Read some records
        $reader->nextRecord();
        $reader->nextRecord();
        $this->assertEquals(1, $reader->getCurrentPosition());

        // Rewind
        $reader->rewind();
        $this->assertEquals(-1, $reader->getCurrentPosition());

        // Should be able to read from beginning again
        $record = $reader->nextRecord();
        $this->assertEquals($this->testData[1], $record);
        $this->assertEquals(0, $reader->getCurrentPosition());
    }

    #[Test]
    public function test_has_records(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);
        $this->assertTrue($reader->hasRecords());
    }

    #[Test]
    public function test_has_next(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        // Initially should have next
        $this->assertTrue($reader->hasNext());

        // Read all records
        while ($reader->nextRecord() !== false) {
            // Continue reading
        }

        // Should not have next anymore
        $this->assertFalse($reader->hasNext());
    }

    #[Test]
    public function test_set_source(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader();
        $reader->setSource($this->testFile);

        $this->assertEquals($this->testFile, $reader->getSource());
        $this->assertEquals(4, $reader->getRecordCount());
    }

    #[Test]
    public function test_set_config(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = new CsvReader($this->testFile);

        $newConfig = new CsvConfig();
        $newConfig->setPath($this->testFile) // Set the file path
                  ->setDelimiter(';')
                  ->setHasHeader(false);

        $reader->setConfig($newConfig);

        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_empty_file_throws_exception(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $emptyFile = self::TEST_DATA_DIR . '/empty.csv';
        file_put_contents($emptyFile, '');

        $this->expectException(EmptyFileException::class);

        $reader = new CsvReader($emptyFile);
        $reader->getRecordCount();
    }

    #[Test]
    #[DataProvider('csvConfigProvider')]
    public function test_different_csv_configurations(CsvConfig $config, array $expectedData): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        // Create test file with custom delimiter
        $testFile = self::TEST_DATA_DIR . '/custom_config.csv';
        $writer = new SplCsvWriter($testFile, $config);

        foreach ($expectedData as $row) {
            $writer->write($row);
        }
        unset($writer);

        $reader = new CsvReader($testFile, $config);

        if ($config->hasHeader()) {
            $header = $reader->getHeader();
            $this->assertEquals($expectedData[0], $header);

            $record = $reader->nextRecord();
            $this->assertEquals($expectedData[1], $record);
        } else {
            $record = $reader->nextRecord();
            $this->assertEquals($expectedData[0], $record);
        }
    }

    public static function csvConfigProvider(): array
    {
        return [
            'semicolon delimiter' => [
                (new CsvConfig())->setDelimiter(';'),
                [['col1', 'col2'], ['value1', 'value2']],
            ],
            'custom enclosure' => [
                (new CsvConfig())->setEnclosure("'"),
                [['col1', 'col2'], ['value1', 'value2']],
            ],
            'tab delimiter' => [
                (new CsvConfig())->setDelimiter("\t"),
                [['col1', 'col2'], ['value1', 'value2']],
            ],
            'no headers' => [
                (new CsvConfig())->setHasHeader(false),
                [['value1', 'value2'], ['value3', 'value4']],
            ],
        ];
    }

    #[Test]
    public function test_malformed_csv_handling(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $malformedData = "col1,col2\nvalue1,\"unclosed quote\nvalue3,value4";
        $malformedFile = self::TEST_DATA_DIR . '/malformed.csv';
        file_put_contents($malformedFile, $malformedData);

        $reader = new CsvReader($malformedFile);

        $header = $reader->getHeader();
        $this->assertEquals(['col1', 'col2'], $header);

        // FastCSV should handle malformed data gracefully
        $record = $reader->nextRecord();
        $this->assertIsArray($record);
    }

    #[Test]
    public function test_unicode_content(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $unicodeData = [
            ['Name', 'Description'],
            ['José', 'Café owner'],
            ['München', 'German city'],
            ['北京', 'Capital of China'],
        ];

        $unicodeFile = self::TEST_DATA_DIR . '/unicode.csv';
        $writer = new SplCsvWriter($unicodeFile);
        foreach ($unicodeData as $row) {
            $writer->write($row);
        }
        unset($writer);

        $reader = new CsvReader($unicodeFile);

        $header = $reader->getHeader();
        $this->assertEquals(['Name', 'Description'], $header);

        $record1 = $reader->nextRecord();
        $this->assertEquals(['José', 'Café owner'], $record1);

        $record2 = $reader->nextRecord();
        $this->assertEquals(['München', 'German city'], $record2);

        $record3 = $reader->nextRecord();
        $this->assertEquals(['北京', 'Capital of China'], $record3);
    }

    #[Test]
    public function test_large_file_performance(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $largeFile = self::TEST_DATA_DIR . '/large.csv';
        $writer = new SplCsvWriter($largeFile);

        // Write header
        $writer->write(['id', 'name', 'email']);

        // Write 1000 records
        for ($i = 1; $i <= 1000; $i++) {
            $writer->write([$i, "User $i", "user$i@example.com"]);
        }
        unset($writer);

        $reader = new CsvReader($largeFile);

        $this->assertEquals(1000, $reader->getRecordCount());

        // Test seeking to middle
        $record = $reader->seek(500);
        $this->assertEquals(['501', 'User 501', 'user501@example.com'], $record);

        // Test seeking to end
        $record = $reader->seek(999);
        $this->assertEquals(['1000', 'User 1000', 'user1000@example.com'], $record);
    }
}
