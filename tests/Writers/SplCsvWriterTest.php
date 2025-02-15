<?php

namespace Tests\Writers;

use Faker\Factory as FakerFactory;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SplCsvWriter::class)]
final class SplCsvWriterTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__.'/data';

    private const string TEST_CSV = self::TEST_DATA_DIR.'/test.csv';

    private const int PERFORMANCE_RECORDS = 1000;

    private const float MAX_EXECUTION_TIME = 1.0;

    private const int MAX_MEMORY_USAGE = 10 * 1024 * 1024; // 10MB

    private CsvConfig $defaultConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->initializeConfigs();
    }

    private function setupTestDirectory(): void
    {
        if (! is_dir(self::TEST_DATA_DIR)) {
            mkdir(self::TEST_DATA_DIR, 0777, true);
        }
    }

    private function initializeConfigs(): void
    {
        $this->defaultConfig = (new CsvConfig)
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->setEscape('\\');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestFiles();
        $this->cleanupTestDirectory();
        unset($this->defaultConfig);
    }

    private function cleanupTestFiles(): void
    {
        if (file_exists(self::TEST_CSV)) {
            unlink(self::TEST_CSV);
        }
    }

    private function cleanupTestDirectory(): void
    {
        if (is_dir(self::TEST_DATA_DIR)) {
            rmdir(self::TEST_DATA_DIR);
        }
    }

    #[Test]
    public function write_should_create_valid_csv_with_default_config(): void
    {
        $data = [
            ['name', 'score'],
            ['John Doe', '95'],
            ['Jane Smith', '88'],
        ];

        $writer = new SplCsvWriter(self::TEST_CSV, $this->defaultConfig);
        $this->writeRecords($writer, $data);

        $this->assertFileExists(self::TEST_CSV);
        $this->assertFileContent(
            "name,score\n".
            "\"John Doe\",95\n".
            "\"Jane Smith\",88\n",
            self::TEST_CSV
        );
    }

    #[Test]
    public function write_should_handle_custom_delimiters_and_escaping(): void
    {
        $config = (new CsvConfig)
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('\\');

        $data = [
            ['name', 'description'],
            ['product', 'contains;semicolon'],
            ["O'Brien", "has'quote"],
        ];

        $writer = new SplCsvWriter(self::TEST_CSV, $config);
        $this->writeRecords($writer, $data);

        $this->assertFileExists(self::TEST_CSV);
        $this->assertFileContent(
            "name;description\n".
            "product;'contains;semicolon'\n".
            "'O\\'Brien';'has\\'quote'\n",
            self::TEST_CSV
        );
    }

    #[Test]
    public function write_should_handle_unicode_characters(): void
    {

        $data = [
            ['name', 'text'],
            ['José', '🌟 Unicode test'],
            ['München', '中文测试'],
            ['Français', 'ñçåéëþüúíóö'],
        ];

        $writer = new SplCsvWriter(self::TEST_CSV, $this->defaultConfig);
        $this->writeRecords($writer, $data);

        $this->assertFileExists(self::TEST_CSV);
        $this->assertFileContent(
            "name,text\n".
            "José,\"🌟 Unicode test\"\n".
            "München,中文测试\n".
            "Français,ñçåéëþüúíóö\n",
            self::TEST_CSV
        );
    }

    #[Test]
    #[DataProvider('provideConfigTestCases')]
    public function write_should_handle_different_configurations(
        CsvConfig $config,
        array $data,
        string $expected
    ): void {

        $writer = new SplCsvWriter(self::TEST_CSV, $config);
        $this->writeRecords($writer, $data);

        $this->assertFileExists(self::TEST_CSV);
        $this->assertFileContent($expected, self::TEST_CSV);
    }

    public static function provideConfigTestCases(): array
    {
        return [
            'tab_delimiter' => [
                (new CsvConfig)
                    ->setDelimiter("\t")
                    ->setEnclosure('"'),
                [['col1', 'col2'], ['val1', 'val2']],
                "col1\tcol2\nval1\tval2\n",
            ],
            'pipe_delimiter' => [
                (new CsvConfig)
                    ->setDelimiter('|')
                    ->setEnclosure('"'),
                [['a', 'b'], ['1', '2']],
                "a|b\n1|2\n",
            ],
            'custom_enclosure' => [
                (new CsvConfig)
                    ->setDelimiter(',')
                    ->setEnclosure('*')
                    ->setEscape('\\'),
                [['data', 'with,comma'], ['quoted', 'value']],
                "data,*with,comma*\nquoted,value\n",
            ],
        ];
    }

    #[Test]
    #[Group('performance')]
    public function write_should_meet_performance_requirements(): void
    {

        $records = $this->generateLargeDataset();
        $startMemory = memory_get_usage(true);

        $executionTime = $this->measureWritePerformance($records);
        $memoryUsed = memory_get_usage(true) - $startMemory;

        $this->assertPerformanceMetrics($executionTime, $memoryUsed);
    }

    private function generateLargeDataset(): array
    {
        $faker = FakerFactory::create();
        $records = [];

        for ($i = 0; $i < self::PERFORMANCE_RECORDS; $i++) {
            $records[] = [
                $faker->uuid,
                $faker->name,
                $faker->email,
                $faker->text(100),
            ];
        }

        return $records;
    }

    private function measureWritePerformance(array $records): float
    {
        $startTime = microtime(true);

        $writer = new SplCsvWriter(self::TEST_CSV, $this->defaultConfig);
        $this->writeRecords($writer, $records);

        return microtime(true) - $startTime;
    }

    private function assertPerformanceMetrics(float $executionTime, int $memoryUsed): void
    {
        $this->assertFileExists(self::TEST_CSV);
        $this->assertLessThan(
            self::MAX_EXECUTION_TIME,
            $executionTime,
            sprintf('File writing took too long: %.2f seconds', $executionTime)
        );
        $this->assertLessThan(
            self::MAX_MEMORY_USAGE,
            $memoryUsed,
            sprintf('Memory usage exceeded limit: %d bytes', $memoryUsed)
        );
    }

    #[Test]
    public function setTarget_should_update_output_path(): void
    {

        $writer = new SplCsvWriter(null, $this->defaultConfig);

        $writer->setTarget(self::TEST_CSV);

        $this->assertEquals(self::TEST_CSV, $writer->getTarget());
        $writer->write(['test', 'data']);
        $this->assertFileExists(self::TEST_CSV);
    }

    private function writeRecords(SplCsvWriter $writer, array $records): void
    {
        foreach ($records as $record) {
            $writer->write($record);
        }
    }

    private function assertFileContent(string $expected, string $filePath): void
    {
        $this->assertFileExists($filePath, 'Output file was not created');
        $content = file_get_contents($filePath);
        $this->assertNotFalse($content, 'Failed to read output file');
        $this->assertEquals($expected, $content, 'File content does not match expected output');
    }
}
