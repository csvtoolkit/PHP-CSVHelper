<?php

namespace Tests\Writers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Exceptions\CsvWriterException;
use Phpcsv\CsvHelper\Writers\CsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvWriter::class)]
class CsvWriterTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__.'/data';

    private const string TEST_OUTPUT_FILE = self::TEST_DATA_DIR.'/test_output.csv';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
    }

    private function setupTestDirectory(): void
    {
        if (! is_dir(self::TEST_DATA_DIR)) {
            mkdir(self::TEST_DATA_DIR, 0o777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestFiles();
        $this->cleanupTestDirectory();
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

    /**
     * Helper method to read CSV file and normalize line endings
     */
    private function readCsvLines(string $filePath): array
    {
        $content = file_get_contents($filePath);

        // Normalize line endings to \n
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Remove trailing newlines and split
        $lines = explode("\n", trim($content));

        // Filter out empty lines
        return array_filter($lines, fn ($line): bool => $line !== '');
    }

    #[Test]
    public function test_fastcsv_extension_loaded(): void
    {
        $this->assertTrue(extension_loaded('fastcsv'), 'FastCSV extension must be loaded for tests');
    }

    #[Test]
    public function test_constructor_with_target_and_config(): void
    {
        $config = (new CsvConfig())->setDelimiter(';');
        $headers = ['id', 'name', 'email'];

        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, $config, $headers);

        $this->assertEquals(self::TEST_OUTPUT_FILE, $csvWriter->getTarget());
        $this->assertEquals(';', $csvWriter->getConfig()->getDelimiter());
        $this->assertEquals($headers, $csvWriter->getHeaders());
    }

    #[Test]
    public function test_constructor_with_default_config(): void
    {
        $csvWriter = new CsvWriter();
        $this->assertInstanceOf(CsvConfig::class, $csvWriter->getConfig());
        $this->assertNull($csvWriter->getHeaders());
    }

    #[Test]
    public function test_get_writer_returns_fastcsv_writer(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);
        $writer = $csvWriter->getWriter();

        $this->assertInstanceOf(\FastCSVWriter::class, $writer);
    }

    #[Test]
    public function test_write_single_record(): void
    {
        $headers = ['id', 'name', 'email'];
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, null, $headers);

        $record = ['1', 'John Doe', 'john@example.com'];
        $csvWriter->write($record);
        $csvWriter->close();

        // Verify the file was written correctly
        $this->assertFileExists(self::TEST_OUTPUT_FILE);
        $lines = $this->readCsvLines(self::TEST_OUTPUT_FILE);

        $this->assertCount(2, $lines); // Header + data
        $this->assertEquals('id,name,email', $lines[0]);
        $this->assertEquals('1,John Doe,john@example.com', $lines[1]);
    }

    #[Test]
    public function test_write_multiple_records(): void
    {
        $headers = ['id', 'name', 'score'];
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, null, $headers);

        $records = [
            ['1', 'Alice', '95'],
            ['2', 'Bob', '87'],
            ['3', 'Charlie', '92'],
        ];

        foreach ($records as $record) {
            $csvWriter->write($record);
        }
        $csvWriter->close();

        // Verify the file content
        $this->assertFileExists(self::TEST_OUTPUT_FILE);
        $lines = $this->readCsvLines(self::TEST_OUTPUT_FILE);

        $this->assertCount(4, $lines); // Header + 3 data records
        $this->assertEquals('id,name,score', $lines[0]);
        $this->assertEquals('1,Alice,95', $lines[1]);
        $this->assertEquals('2,Bob,87', $lines[2]);
        $this->assertEquals('3,Charlie,92', $lines[3]);
    }

    #[Test]
    public function test_write_all_records(): void
    {
        $headers = ['id', 'name'];
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, null, $headers);

        $records = [
            ['1', 'Alice'],
            ['2', 'Bob'],
            ['3', 'Charlie'],
        ];

        $csvWriter->writeAll($records);
        $csvWriter->close();

        // Verify the file content
        $this->assertFileExists(self::TEST_OUTPUT_FILE);
        $lines = $this->readCsvLines(self::TEST_OUTPUT_FILE);

        $this->assertCount(4, $lines);
    }

    #[Test]
    public function test_write_map_record(): void
    {
        $headers = ['id', 'name', 'email'];
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, null, $headers);

        $recordMap = [
            'id' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $csvWriter->writeMap($recordMap);
        $csvWriter->close();

        // Verify the file was written correctly
        $this->assertFileExists(self::TEST_OUTPUT_FILE);
        $lines = $this->readCsvLines(self::TEST_OUTPUT_FILE);

        $this->assertCount(2, $lines);
        $this->assertEquals('id,name,email', $lines[0]);
        $this->assertEquals('1,John Doe,john@example.com', $lines[1]);
    }

    #[Test]
    public function test_write_without_headers(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);

        $record = ['value1', 'value2', 'value3'];
        $csvWriter->write($record);
        $csvWriter->close();

        $this->assertFileExists(self::TEST_OUTPUT_FILE);

        $lines = $this->readCsvLines(self::TEST_OUTPUT_FILE);
        $this->assertCount(1, $lines);
        $this->assertEquals('value1,value2,value3', $lines[0]);
    }

    #[Test]
    public function test_set_and_get_headers(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);

        $this->assertNull($csvWriter->getHeaders());

        $headers = ['col1', 'col2', 'col3'];
        $csvWriter->setHeaders($headers);

        $this->assertEquals($headers, $csvWriter->getHeaders());
    }

    #[Test]
    public function test_set_headers_recreates_writer(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);

        // Initialize writer by accessing it
        $writer1 = $csvWriter->getWriter();
        $this->assertInstanceOf(\FastCSVWriter::class, $writer1);

        // Set headers should recreate the writer
        $csvWriter->setHeaders(['new', 'headers']);
        $writer2 = $csvWriter->getWriter();

        $this->assertInstanceOf(\FastCSVWriter::class, $writer2);
    }

    #[Test]
    public function test_set_target(): void
    {
        $csvWriter = new CsvWriter();

        $newTarget = self::TEST_DATA_DIR.'/new_target.csv';
        $csvWriter->setTarget($newTarget);

        $this->assertEquals($newTarget, $csvWriter->getTarget());
    }

    #[Test]
    public function test_set_config(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);

        $newConfig = (new CsvConfig())
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('/');

        $csvWriter->setConfig($newConfig);
        $config = $csvWriter->getConfig();

        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
    }

    #[Test]
    #[DataProvider('configProvider')]
    public function test_write_with_different_configs(CsvConfig $config, array $expectedContent): void
    {
        $filePath = self::TEST_DATA_DIR.'/config_test.csv';
        $csvWriter = new CsvWriter($filePath, $config, ['col1', 'col2']);

        $csvWriter->write(['value1', 'value2']);
        $csvWriter->close();

        $this->assertFileExists($filePath);
        $lines = $this->readCsvLines($filePath);

        $this->assertEquals($expectedContent, $lines);
    }

    public static function configProvider(): array
    {
        return [
            'semicolon delimiter' => [
                (new CsvConfig())->setDelimiter(';'),
                ['col1;col2', 'value1;value2'],
            ],
            'single quote enclosure' => [
                (new CsvConfig())->setEnclosure("'"),
                ["col1,col2", "value1,value2"],
            ],
            'tab delimiter' => [
                (new CsvConfig())->setDelimiter("\t"),
                ["col1\tcol2", "value1\tvalue2"],
            ],
            'pipe delimiter' => [
                (new CsvConfig())->setDelimiter('|'),
                ['col1|col2', 'value1|value2'],
            ],
        ];
    }

    #[Test]
    public function test_write_unicode_characters(): void
    {
        $headers = ['name', 'text'];
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE, null, $headers);

        $records = [
            ['José', '🌟 Unicode test'],
            ['München', '中文测试'],
            ['Français', 'ñçåéëþüúíóö'],
        ];

        foreach ($records as $record) {
            $csvWriter->write($record);
        }
        $csvWriter->close();

        // Verify the file content
        $this->assertFileExists(self::TEST_OUTPUT_FILE);
        $content = file_get_contents(self::TEST_OUTPUT_FILE);

        $this->assertStringContainsString('José', $content);
        $this->assertStringContainsString('🌟 Unicode test', $content);
        $this->assertStringContainsString('中文测试', $content);
        $this->assertStringContainsString('ñçåéëþüúíóö', $content);
    }

    #[Test]
    public function test_exception_on_empty_target(): void
    {
        $this->expectException(CsvWriterException::class);
        $this->expectExceptionMessage('Target file path is required');

        $csvWriter = new CsvWriter();
        $csvWriter->getWriter(); // Should throw exception
    }

    #[Test]
    public function test_close_writer(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);
        $writer = $csvWriter->getWriter();

        $this->assertInstanceOf(\FastCSVWriter::class, $writer);

        // Close should not throw any exceptions
        $csvWriter->close();
        $this->assertTrue(true); // If we reach here, close() worked
    }

    #[Test]
    public function test_destructor_closes_writer(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);
        $csvWriter->getWriter(); // Initialize writer

        // Destructor should be called when object goes out of scope
        unset($csvWriter);

        $this->assertTrue(true); // If we reach here, destructor worked without errors
    }

    #[Test]
    public function test_reset_functionality(): void
    {
        $csvWriter = new CsvWriter(self::TEST_OUTPUT_FILE);
        $csvWriter->getWriter(); // Initialize writer

        // Reset should clear internal state
        $csvWriter->reset();

        // Should be able to get writer again
        $newWriter = $csvWriter->getWriter();
        $this->assertInstanceOf(\FastCSVWriter::class, $newWriter);
    }

    #[Test]
    public function test_large_file_writing(): void
    {
        $largeFilePath = self::TEST_DATA_DIR.'/large_output.csv';
        $headers = ['id', 'name', 'email', 'score'];
        $csvWriter = new CsvWriter($largeFilePath, null, $headers);

        // Write 1000 records
        for ($i = 1; $i <= 1000; $i++) {
            $csvWriter->write([$i, "User $i", "user$i@example.com", random_int(1, 100)]);
        }
        $csvWriter->close();

        $this->assertFileExists($largeFilePath);

        // Verify file has correct number of lines (header + 1000 records)
        $lines = $this->readCsvLines($largeFilePath);
        $this->assertCount(1001, $lines);

        // Verify header
        $this->assertEquals('id,name,email,score', $lines[0]);

        // Verify first and last records
        $this->assertStringStartsWith('1,User 1,user1@example.com,', $lines[1]);
        $this->assertStringStartsWith('1000,User 1000,user1000@example.com,', $lines[1000]);
    }
}
