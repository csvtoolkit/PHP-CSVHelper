<?php

namespace Tests\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Factories\WriterFactory;
use CsvToolkit\Helpers\ExtensionHelper;
use CsvToolkit\Writers\CsvWriter;
use CsvToolkit\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(WriterFactory::class)]
class WriterFactoryTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->testFile = self::TEST_DATA_DIR . '/writer_factory_test.csv';
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
    public function test_create_returns_csv_writer_when_fastcsv_available(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $writer = WriterFactory::create($this->testFile);
        $this->assertInstanceOf(CsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_returns_spl_writer_when_fastcsv_not_available(): void
    {
        if (ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test fallback');
        }

        $writer = WriterFactory::create($this->testFile);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_with_target_file(): void
    {
        $writer = WriterFactory::create($this->testFile);
        $this->assertEquals($this->testFile, $writer->getTarget());
        $this->assertEquals($this->testFile, $writer->getSource());
        $this->assertEquals($this->testFile, $writer->getDestination());
    }

    #[Test]
    public function test_create_with_csv_config(): void
    {
        $config = new CsvConfig();
        $config->setDelimiter(';')->setHasHeader(false);

        $writer = WriterFactory::create($this->testFile, $config);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
        $this->assertFalse($writer->getConfig()->hasHeader());
    }

    #[Test]
    public function test_create_with_spl_config(): void
    {
        $config = new SplConfig();
        $config->setDelimiter(';')->setHasHeader(false);

        $writer = WriterFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
        $this->assertFalse($writer->getConfig()->hasHeader());
    }

    #[Test]
    public function test_create_fast_csv_always_returns_csv_writer(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $writer = WriterFactory::createFastCsv($this->testFile);
        $this->assertInstanceOf(CsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_fast_csv_with_target_and_config(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = new CsvConfig();
        $config->setDelimiter("\t");

        $writer = WriterFactory::createFastCsv($this->testFile, $config);
        $this->assertInstanceOf(CsvWriter::class, $writer);
        $this->assertEquals($this->testFile, $writer->getTarget());
        $this->assertEquals("\t", $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_spl_always_returns_spl_writer(): void
    {
        $writer = WriterFactory::createSpl($this->testFile);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_spl_with_target_and_config(): void
    {
        $config = new SplConfig();
        $config->setDelimiter("\t");

        $writer = WriterFactory::createSpl($this->testFile, $config);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals($this->testFile, $writer->getTarget());
        $this->assertEquals("\t", $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_null_parameters(): void
    {
        $writer = WriterFactory::create('');
        $this->assertEquals('', $writer->getTarget());
        $this->assertEquals('', $writer->getSource());
        $this->assertEquals('', $writer->getDestination());
    }

    #[Test]
    public function test_create_with_empty_target(): void
    {
        $writer = WriterFactory::create('');
        $this->assertEquals('', $writer->getTarget());
    }

    #[Test]
    public function test_factory_creates_fresh_instances(): void
    {
        $writer1 = WriterFactory::create($this->testFile);
        $writer2 = WriterFactory::create($this->testFile);

        $this->assertNotSame($writer1, $writer2);
    }

    #[Test]
    public function test_create_with_spl_config_converts_to_appropriate_writer(): void
    {
        $config = new SplConfig();
        $config->setDelimiter(';');

        $writer = WriterFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_csv_config_prefers_fastcsv_when_available(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';');

        $writer = WriterFactory::create($this->testFile, $config);
        $this->assertInstanceOf(CsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_with_csv_config_falls_back_to_spl_when_fastcsv_unavailable(): void
    {
        if (ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test fallback');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';');

        $writer = WriterFactory::create($this->testFile, $config);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_factory_handles_config_conversion(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $writer = WriterFactory::createSpl($this->testFile, $splConfig);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
        $this->assertEquals("'", $writer->getConfig()->getEnclosure());
        $this->assertFalse($writer->getConfig()->hasHeader());
    }

    #[Test]
    public function test_writer_can_write_test_data(): void
    {
        $writer = WriterFactory::create($this->testFile);

        $testData = [
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
        ];

        foreach ($testData as $record) {
            $writer->write($record);
        }

        $this->assertFileExists($this->testFile);
        $content = file_get_contents($this->testFile);
        $this->assertStringContainsString('Name,Age,Email', $content);
        $this->assertStringContainsString('"John Doe",30,john@example.com', $content);
    }

    #[Test]
    public function test_factory_preserves_config_settings(): void
    {
        $config = new CsvConfig();
        $config->setDelimiter(';')
               ->setEnclosure("'")
               ->setEscape('/')
               ->setHasHeader(false);

        $writer = WriterFactory::create($this->testFile, $config);
        $writerConfig = $writer->getConfig();

        $this->assertEquals(';', $writerConfig->getDelimiter());
        $this->assertEquals("'", $writerConfig->getEnclosure());
        $this->assertEquals('/', $writerConfig->getEscape());
        $this->assertFalse($writerConfig->hasHeader());
    }

    #[Test]
    public function test_factory_handles_nonexistent_target_gracefully(): void
    {
        $writer = WriterFactory::create('/nonexistent/directory/file.csv');
        $this->assertEquals('/nonexistent/directory/file.csv', $writer->getTarget());
    }

    #[Test]
    public function test_create_fast_csv_with_spl_config(): void
    {
        if (! ExtensionHelper::isFastCsvAvailable()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';');

        $writer = WriterFactory::createFastCsv($this->testFile, $splConfig);
        $this->assertInstanceOf(CsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_spl_with_csv_config(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';');

        $writer = WriterFactory::createSpl($this->testFile, $splConfig);
        $this->assertInstanceOf(SplCsvWriter::class, $writer);
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_writer_aliases_work_correctly(): void
    {
        $writer = WriterFactory::create($this->testFile);

        $this->assertEquals($this->testFile, $writer->getSource());
        $this->assertEquals($this->testFile, $writer->getTarget());
        $this->assertEquals($this->testFile, $writer->getDestination());

        $newFile = self::TEST_DATA_DIR . '/new_file.csv';

        $writer->setSource($newFile);
        $this->assertEquals($newFile, $writer->getSource());
        $this->assertEquals($newFile, $writer->getTarget());
        $this->assertEquals($newFile, $writer->getDestination());

        $writer->setTarget($this->testFile);
        $this->assertEquals($this->testFile, $writer->getSource());
        $this->assertEquals($this->testFile, $writer->getTarget());
        $this->assertEquals($this->testFile, $writer->getDestination());

        $writer->setDestination($newFile);
        $this->assertEquals($newFile, $writer->getSource());
        $this->assertEquals($newFile, $writer->getTarget());
        $this->assertEquals($newFile, $writer->getDestination());
    }

    #[Test]
    public function test_writer_factory_with_write_all(): void
    {
        $writer = WriterFactory::create($this->testFile);

        $testData = [
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
        ];

        $writer->writeAll($testData);

        $this->assertFileExists($this->testFile);
        $lines = file($this->testFile, FILE_IGNORE_NEW_LINES);
        $this->assertCount(3, $lines);
        $this->assertEquals('Name,Age,Email', $lines[0]);
        $this->assertEquals('"John Doe",30,john@example.com', $lines[1]);
        $this->assertEquals('"Jane Smith",25,jane@example.com', $lines[2]);
    }
}
