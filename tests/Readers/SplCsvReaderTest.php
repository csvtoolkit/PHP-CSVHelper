<?php

namespace Tests\Readers;

use Faker\Factory as FakerFactory;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Exceptions\EmptyFileException;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Exceptions\InvalidConfigurationException;
use Phpcsv\CsvHelper\Readers\SplCsvReader;
use Phpcsv\CsvHelper\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SplCsvReader::class)]
class SplCsvReaderTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__.'/data';

    private const string SAMPLE_CSV = self::TEST_DATA_DIR.'/sample.csv';

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
        $this->defaultConfig = new CsvConfig;
        $this->createSampleData();
    }

    private function setupTestDirectory(): void
    {
        if (! is_dir(self::TEST_DATA_DIR)) {
            mkdir(self::TEST_DATA_DIR, 0777, true);
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
        $writer->write(['name', 'score']);
        $this->data[] = ['name', 'score'];

        for ($i = 0; $i < self::SAMPLE_RECORDS; $i++) {
            $record = [$faker->name, $faker->numberBetween(1, 100)];
            $writer->write($record);
            $this->data[] = $record;
        }
    }

    protected function createTestFile(array $records): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_test_');
        $writer = new SplCsvWriter($path, $this->defaultConfig);

        foreach ($records as $record) {
            $writer->write($record);
        }

        // Ensure file is written and closed
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

    public function test_read_csv_can_count_sample_records(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath($this->filePath);
        $count = $csvReader->getRecordCount();
        $this->assertEquals(count($this->data) - 1, $count);
    }

    public function test_read_csv_can_get_header(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $header = $csvReader->getHeader();
        $this->assertEquals(['name', 'score'], $header);
    }

    public function test_read_csv_can_get_record(): void
    {
        $data = $this->data;
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => 'name', '1' => 'score'], $record);
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => $data[1][0], '1' => $data[1][1]], $record);
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => $data[2][0], '1' => $data[2][1]], $record);
    }

    public function test_read_csv_can_get_current_position(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $csvReader->getRecord();
        $csvReader->getRecord();
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(2, $position);
    }

    public function test_read_csv_can_rewind(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $csvReader->getRecord();
        $csvReader->getRecord();
        $csvReader->rewind();
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(0, $position);
    }

    public function test_read_csv_can_seek(): void
    {
        $data = $this->data;
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $csvReader->rewind();
        $record = $csvReader->seek(3);
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(3, $position);
        $this->assertEquals(['0' => $data[2][0], '1' => $data[2][1]], $record);
    }

    public function test_read_csv_with_empty_file(): void
    {
        $this->expectException(EmptyFileException::class);
        $emptyFilePath = self::TEST_DATA_DIR.'/empty.csv';

        if (! is_dir(dirname($emptyFilePath))) {
            mkdir(dirname($emptyFilePath), 0777, true);
        }

        file_put_contents($emptyFilePath, '');

        $csvReader = new SplCsvReader;
        $csvReader->setSource($emptyFilePath);
        $csvReader->getRecordCount();
    }

    public function test_read_csv_with_nonexistent_file(): void
    {
        $this->expectException(FileNotFoundException::class);

        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::TEST_DATA_DIR.'/nonexistent.csv');
        $csvReader->getRecordCount();
    }

    public function test_read_csv_can_get_source(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(self::SAMPLE_CSV);
        $source = $csvReader->getSource();
        $this->assertEquals(self::SAMPLE_CSV, $source);
    }

    public function test_read_csv_can_set_source(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->setSource(self::SAMPLE_CSV);
        $source = $csvReader->getSource();
        $this->assertEquals(self::SAMPLE_CSV, $source);
    }

    public function test_read_csv_can_set_config(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->setSource(self::SAMPLE_CSV);
        $desiredConfig = (new CsvConfig)
            ->setDelimiter('@')
            ->setEnclosure('"')
            ->setEscape('\\');

        $csvReader->setConfig($desiredConfig);
        $config = $csvReader->getConfig();
        $this->assertEquals($desiredConfig->getDelimiter(), $config->getDelimiter());
        $this->assertEquals($desiredConfig->getEnclosure(), $config->getEnclosure());
        $this->assertEquals($desiredConfig->getEscape(), $config->getEscape());

    }

    public function test_read_csv_with_malformed_data(): void
    {
        $malformedData = "col1,col2\nvalue1,\"unclosed quote\nvalue3,value4";
        file_put_contents(self::SAMPLE_CSV, $malformedData);

        $csvReader = new SplCsvReader;
        $csvReader->setSource(self::SAMPLE_CSV);

        $record = $csvReader->getRecord();
        $this->assertEquals(['col1', 'col2'], $record);

        $record = $csvReader->getRecord();

        $this->assertEquals(['value1', "unclosed quote\nvalue3,value4"], $record);
    }

    /**
     * @throws InvalidConfigurationException
     */
    #[Test]
    #[DataProvider('configProvider')]
    public function read_csv_with_different_configs(CsvConfig $config, string $expected): void
    {
        // Arrange
        $data = [
            ['col1', 'col2'],
            ['value1', 'value2'],
        ];
        $filePath = $this->createConfiguredTestFile($data, $config);

        // Act
        $csvReader = new SplCsvReader;
        $csvReader->setSource($filePath);
        $csvReader->setConfig($config);

        // Assert
        $this->assertEquals(['col1', 'col2'], $csvReader->getRecord());
        $this->assertEquals(['value1', 'value2'], $csvReader->getRecord());
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function createConfiguredTestFile(array $data, CsvConfig $config): string
    {
        $filePath = self::TEST_DATA_DIR.'/test_config.csv';
        $writer = new SplCsvWriter($filePath, $config);

        foreach ($data as $row) {
            $writer->write($row);
        }

        return $filePath;
    }

    public static function configProvider(): array
    {
        return [
            'custom delimiter' => [
                (new CsvConfig)->setDelimiter('@'),
                '@',
            ],
            'custom enclosure' => [
                (new CsvConfig)->setEnclosure('\''),
                '\'',
            ],
            'tab delimiter' => [
                (new CsvConfig)->setDelimiter("\t"),
                "\t",
            ],
            'pipe delimiter' => [
                (new CsvConfig)->setDelimiter('|'),
                '|',
            ],
        ];
    }

    public static function invalidConfigProvider(): array
    {
        return [
            'empty delimiter' => [
                (new CsvConfig)->setDelimiter(''),
                InvalidConfigurationException::class,
            ],
        ];
    }

    #[Test]
    public function test_read_csv_with_unicode_characters(): void
    {
        $unicodeData = [
            ['name', 'text'],
            ['José', '🌟 Unicode test'],
            ['München', '中文测试'],
            ['Français', 'ñçåéëþüúíóö'],
        ];

        $filePath = $this->createTestFile($unicodeData);
        $csvReader = new SplCsvReader($filePath);

        $record = $csvReader->getRecord();
        $this->assertEquals(['name', 'text'], $record);

        $record = $csvReader->getRecord();
        $this->assertEquals(['José', '🌟 Unicode test'], $record);

        $record = $csvReader->getRecord();
        $this->assertEquals(['München', '中文测试'], $record);

        $record = $csvReader->getRecord();
        $this->assertEquals(['Français', 'ñçåéëþüúíóö'], $record);

        unlink($filePath);
    }

    #[Test]
    public function test_skip_header(): void
    {
        $data = [
            ['header1', 'header2'],
            ['value1', 'value2'],
        ];

        $filePath = $this->createTestFile($data);
        $csvReader = new SplCsvReader($filePath);
        $csvReader->getConfig()->setHasHeader(true);

        $csvReader->skipHeader();
        $this->assertEquals(['value1', 'value2'], $csvReader->getRecord());

        unlink($filePath);
    }
}
