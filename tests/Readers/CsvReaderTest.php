<?php

namespace Tests\Readers;

use Faker\Factory as FakerFactory;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Exceptions\InvalidConfigurationException;
use Phpcsv\CsvHelper\Readers\CsvReader;
use Phpcsv\CsvHelper\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvReader::class)]
class CsvReaderTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__.'/data';

    private const string SAMPLE_CSV = self::TEST_DATA_DIR.'/fastcsv_sample.csv';

    private const int SAMPLE_RECORDS = 20;

    private array $data = [];

    private string $filePath;

    private CsvConfig $defaultConfig;

    /**
     * @throws InvalidConfigurationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->defaultConfig = new CsvConfig();
        $this->createSampleData();
    }

    private function setupTestDirectory(): void
    {
        if (! is_dir(self::TEST_DATA_DIR)) {
            mkdir(self::TEST_DATA_DIR, 0o777, true);
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function createSampleData(): void
    {
        $faker = FakerFactory::create();
        $this->filePath = self::SAMPLE_CSV;

        $writer = new SplCsvWriter($this->filePath, $this->defaultConfig);
        $writer->write(['name', 'score', 'email']);
        $this->data[] = ['name', 'score', 'email'];

        for ($i = 0; $i < self::SAMPLE_RECORDS; $i++) {
            $record = [$faker->name, $faker->numberBetween(1, 100), $faker->email];
            $writer->write($record);
            $this->data[] = $record;
        }
    }

    protected function createTestFile(array $records): string
    {
        $path = tempnam(sys_get_temp_dir(), 'fastcsv_test_');
        $writer = new SplCsvWriter($path, $this->defaultConfig);

        foreach ($records as $record) {
            $writer->write($record);
        }

        unset($writer);
        clearstatcache(true, $path);

        return $path;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestFiles();
        $this->cleanupTestDirectory();
        unset($this->data, $this->defaultConfig);
    }

    private function cleanupTestFiles(): void
    {
        array_map('unlink', glob(self::TEST_DATA_DIR.'/*.csv'));
    }

    private function cleanupTestDirectory(): void
    {
        if (is_dir(self::TEST_DATA_DIR)) {
            rmdir(self::TEST_DATA_DIR);
        }
    }

    #[Test]
    public function test_fastcsv_extension_loaded(): void
    {
        $this->assertTrue(extension_loaded('fastcsv'), 'FastCSV extension must be loaded for tests');
    }

    #[Test]
    public function test_constructor_with_source_and_config(): void
    {
        $config = (new CsvConfig())->setDelimiter(';');
        $csvReader = new CsvReader($this->filePath, $config);

        $this->assertEquals($this->filePath, $csvReader->getSource());
        $this->assertEquals(';', $csvReader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_constructor_with_default_config(): void
    {
        $csvReader = new CsvReader();
        $this->assertInstanceOf(CsvConfig::class, $csvReader->getConfig());
    }

    #[Test]
    public function test_get_reader_returns_fastcsv_reader(): void
    {
        $csvReader = new CsvReader($this->filePath);
        $reader = $csvReader->getReader();

        $this->assertInstanceOf(\FastCSVReader::class, $reader);
    }

    #[Test]
    public function test_read_csv_can_count_sample_records(): void
    {
        $csvReader = new CsvReader($this->filePath);
        $count = $csvReader->getRecordCount();

        $this->assertEquals(self::SAMPLE_RECORDS, $count);
    }

    #[Test]
    public function test_read_csv_can_get_header(): void
    {
        $csvReader = new CsvReader($this->filePath);
        $header = $csvReader->getHeader();

        $this->assertEquals(['name', 'score', 'email'], $header);
    }

    #[Test]
    public function test_read_csv_can_get_record(): void
    {
        $data = $this->data;
        $csvReader = new CsvReader($this->filePath);

        $record = $csvReader->getRecord();
        $this->assertEquals($data[1], $record);

        $record = $csvReader->getRecord();
        $this->assertEquals($data[2], $record);
    }

    #[Test]
    public function test_read_csv_can_seek(): void
    {
        $data = $this->data;
        $csvReader = new CsvReader($this->filePath);

        $record = $csvReader->seek(2);
        $this->assertEquals(3, $csvReader->getCurrentPosition());
        $this->assertEquals($data[3], $record);
    }

    #[Test]
    public function test_has_records(): void
    {
        $csvReader = new CsvReader($this->filePath);
        $this->assertTrue($csvReader->hasRecords());
    }
}
