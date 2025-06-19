<?php

namespace Tests\Readers;

use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Readers\SplCsvReader;
use CsvToolkit\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileObject;

#[CoversClass(SplCsvReader::class)]
class SplCsvReaderTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';
    private const string SAMPLE_CSV = self::TEST_DATA_DIR . '/spl_sample.csv';

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
        $reader = new SplCsvReader();

        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertInstanceOf(SplConfig::class, $reader->getConfig());
        $this->assertEquals('', $reader->getSource());
        $this->assertEquals(-1, $reader->getCurrentPosition());
    }

    #[Test]
    public function test_constructor_with_source_only(): void
    {
        $reader = new SplCsvReader($this->testFile);

        $this->assertEquals($this->testFile, $reader->getSource());
        $this->assertInstanceOf(SplConfig::class, $reader->getConfig());
    }

    #[Test]
    public function test_constructor_with_custom_config(): void
    {
        $config = new SplConfig();
        $config->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $reader = new SplCsvReader($this->testFile, $config);

        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertEquals("'", $reader->getConfig()->getEnclosure());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_get_reader_returns_spl_file_object(): void
    {
        $reader = new SplCsvReader($this->testFile);
        $splFileObject = $reader->getReader();

        $this->assertInstanceOf(SplFileObject::class, $splFileObject);
    }

    #[Test]
    public function test_get_reader_with_nonexistent_file_throws_exception(): void
    {
        $this->expectException(FileNotFoundException::class);

        $reader = new SplCsvReader('/nonexistent/file.csv');
        $reader->getReader();
    }

    #[Test]
    public function test_get_record_count(): void
    {
        $reader = new SplCsvReader($this->testFile);
        $count = $reader->getRecordCount();

        // Should return 4 (excluding header)
        $this->assertEquals(4, $count);
    }

    #[Test]
    public function test_next_record_sequential_reading(): void
    {
        $reader = new SplCsvReader($this->testFile);

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
        $reader = new SplCsvReader($this->testFile);

        // Seek to position 2
        $record = $reader->seek(2);
        $this->assertEquals($this->testData[3], $record);
        $this->assertEquals(2, $reader->getCurrentPosition());

        // getRecord should return the same record
        $cachedRecord = $reader->getRecord();
        $this->assertEquals($record, $cachedRecord);
    }

    #[Test]
    public function test_rewind_functionality(): void
    {
        $reader = new SplCsvReader($this->testFile);

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
        $reader = new SplCsvReader($this->testFile);
        $this->assertTrue($reader->hasRecords());
    }

    #[Test]
    public function test_has_next(): void
    {
        $reader = new SplCsvReader($this->testFile);

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
    public function test_unicode_content(): void
    {
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

        $reader = new SplCsvReader($unicodeFile);

        $header = $reader->getHeader();
        $this->assertEquals(['Name', 'Description'], $header);

        $record1 = $reader->nextRecord();
        $this->assertEquals(['José', 'Café owner'], $record1);

        $record2 = $reader->nextRecord();
        $this->assertEquals(['München', 'German city'], $record2);

        $record3 = $reader->nextRecord();
        $this->assertEquals(['北京', 'Capital of China'], $record3);
    }
}
