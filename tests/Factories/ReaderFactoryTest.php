<?php

namespace Tests\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Factories\ReaderFactory;
use CsvToolkit\Helpers\ExtensionHelper;
use CsvToolkit\Readers\CsvReader;
use CsvToolkit\Readers\SplCsvReader;
use CsvToolkit\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReaderFactory::class)]
class ReaderFactoryTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    private string $testFile;

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
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
            ['Bob Johnson', '35', 'bob@example.com'],
        ];

        $this->testFile = $this->createTestFile($this->testData);
    }

    private function createTestFile(array $data): string
    {
        $filePath = self::TEST_DATA_DIR . '/reader_factory_test.csv';
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
    public function test_create_returns_csv_reader_when_fastcsv_available(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $reader = ReaderFactory::create();
        $this->assertInstanceOf(CsvReader::class, $reader);
    }

    #[Test]
    public function test_create_returns_spl_reader_when_fastcsv_not_available(): void
    {
        if (ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test fallback');
        }

        $reader = ReaderFactory::create();
        $this->assertInstanceOf(SplCsvReader::class, $reader);
    }

    #[Test]
    public function test_create_with_source_file(): void
    {
        $reader = ReaderFactory::create($this->testFile);
        $this->assertEquals($this->testFile, $reader->getSource());
    }

    #[Test]
    public function test_create_with_csv_config(): void
    {
        $config = new CsvConfig();
        $config->setDelimiter(';')->setHasHeader(false);

        $reader = ReaderFactory::create($this->testFile, $config);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_create_with_spl_config(): void
    {
        $config = new SplConfig();
        $config->setDelimiter(';')->setHasHeader(false);

        $reader = ReaderFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_create_fast_csv_always_returns_csv_reader(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $reader = ReaderFactory::createFastCsv();
        $this->assertInstanceOf(CsvReader::class, $reader);
    }

    #[Test]
    public function test_create_fast_csv_with_source_and_config(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = new CsvConfig();
        $config->setDelimiter("\t");

        $reader = ReaderFactory::createFastCsv($this->testFile, $config);
        $this->assertInstanceOf(CsvReader::class, $reader);
        $this->assertEquals($this->testFile, $reader->getSource());
        $this->assertEquals("\t", $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_spl_always_returns_spl_reader(): void
    {
        $reader = ReaderFactory::createSpl();
        $this->assertInstanceOf(SplCsvReader::class, $reader);
    }

    #[Test]
    public function test_create_spl_with_source_and_config(): void
    {
        $config = new SplConfig();
        $config->setDelimiter("\t");

        $reader = ReaderFactory::createSpl($this->testFile, $config);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals($this->testFile, $reader->getSource());
        $this->assertEquals("\t", $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_null_parameters(): void
    {
        $reader = ReaderFactory::create();
        $this->assertEquals('', $reader->getSource());
        $this->assertEquals(-1, $reader->getCurrentPosition());
    }

    #[Test]
    public function test_create_with_empty_source(): void
    {
        $reader = ReaderFactory::create('');
        $this->assertEquals('', $reader->getSource());
    }

    #[Test]
    public function test_factory_creates_fresh_instances(): void
    {
        $reader1 = ReaderFactory::create();
        $reader2 = ReaderFactory::create();

        $this->assertNotSame($reader1, $reader2);
    }

    #[Test]
    public function test_create_with_spl_config_converts_to_appropriate_reader(): void
    {
        $config = new SplConfig();
        $config->setDelimiter(';');

        $reader = ReaderFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_csv_config_prefers_fastcsv_when_available(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';');

        $reader = ReaderFactory::create($this->testFile, $config);
        $this->assertInstanceOf(CsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_csv_config_falls_back_to_spl_when_fastcsv_unavailable(): void
    {
        if (ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test fallback');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';');

        $reader = ReaderFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_factory_handles_config_conversion(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $reader = ReaderFactory::createSpl($this->testFile, $splConfig);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
        $this->assertEquals("'", $reader->getConfig()->getEnclosure());
        $this->assertFalse($reader->getConfig()->hasHeader());
    }

    #[Test]
    public function test_reader_can_read_test_data(): void
    {
        $reader = ReaderFactory::create($this->testFile);

        $header = $reader->getHeader();
        $this->assertEquals(['Name', 'Age', 'Email'], $header);

        $firstRecord = $reader->nextRecord();
        $this->assertEquals(['John Doe', '30', 'john@example.com'], $firstRecord);

        $count = $reader->getRecordCount();
        $this->assertEquals(3, $count);
    }

    #[Test]
    public function test_factory_preserves_config_settings(): void
    {
        $config = new CsvConfig();
        $config->setDelimiter(';')
               ->setEnclosure("'")
               ->setEscape('/')
               ->setHasHeader(false);

        $reader = ReaderFactory::create($this->testFile, $config);
        $readerConfig = $reader->getConfig();

        $this->assertEquals(';', $readerConfig->getDelimiter());
        $this->assertEquals("'", $readerConfig->getEnclosure());
        $this->assertEquals('/', $readerConfig->getEscape());
        $this->assertFalse($readerConfig->hasHeader());
    }

    #[Test]
    public function test_factory_handles_nonexistent_source_gracefully(): void
    {
        $reader = ReaderFactory::create('/nonexistent/file.csv');
        $this->assertEquals('/nonexistent/file.csv', $reader->getSource());
    }

    #[Test]
    public function test_create_fast_csv_with_spl_config(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';');

        $reader = ReaderFactory::createFastCsv($this->testFile, $splConfig);
        $this->assertInstanceOf(CsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_spl_with_csv_config(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';');

        $reader = ReaderFactory::createSpl($this->testFile, $splConfig);
        $this->assertInstanceOf(SplCsvReader::class, $reader);
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }
}
