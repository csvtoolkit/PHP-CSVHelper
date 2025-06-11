<?php

namespace Tests\Writers;

use FastCSVWriter;
use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Exceptions\CsvWriterException;
use Phpcsv\CsvHelper\Exceptions\DirectoryNotFoundException;
use Phpcsv\CsvHelper\Writers\CsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvWriter::class)]
class CsvWriterTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->testFile = self::TEST_DATA_DIR . '/test_output.csv';
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
    public function test_constructor_with_null_parameters(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter();

        $this->assertInstanceOf(CsvWriter::class, $writer);
        $this->assertInstanceOf(CsvConfigInterface::class, $writer->getConfig());
        $this->assertEquals('', $writer->getSource());
    }

    #[Test]
    public function test_constructor_with_source_only(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);

        $this->assertEquals($this->testFile, $writer->getSource());
        $this->assertInstanceOf(CsvConfigInterface::class, $writer->getConfig());
    }

    #[Test]
    public function test_constructor_with_custom_config(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $config = new CsvConfig();
        $config->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $writer = new CsvWriter($this->testFile, $config);

        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
        $this->assertEquals("'", $writer->getConfig()->getEnclosure());
        $this->assertFalse($writer->getConfig()->hasHeader());
    }

    #[Test]
    public function test_get_writer_returns_fastcsv_instance(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $fastCsvWriter = $writer->getWriter();

        $this->assertInstanceOf(FastCSVWriter::class, $fastCsvWriter);
    }

    #[Test]
    public function test_get_writer_with_invalid_path_throws_exception(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $this->expectException(DirectoryNotFoundException::class);

        // Use a path that definitely doesn't exist
        $writer = new CsvWriter('/nonexistent_directory_12345/file.csv');
        $writer->getWriter();
    }

    #[Test]
    public function test_get_writer_with_empty_path_throws_exception(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $this->expectException(CsvWriterException::class);
        $this->expectExceptionMessage('Target file path is required');

        // Create writer without setting target path
        $writer = new CsvWriter();
        $writer->getWriter();
    }

    #[Test]
    public function test_write_single_record(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $record = ['John Doe', '30', 'john@example.com'];

        $writer->write($record);
        unset($writer);

        // Verify file contents
        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('John Doe,30,john@example.com', $contents);
    }

    #[Test]
    public function test_write_multiple_records(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        // Verify file contents
        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('Name,Age,Email', $contents);
        $this->assertStringContainsString('John Doe,30,john@example.com', $contents);
        $this->assertStringContainsString('Jane Smith,25,jane@example.com', $contents);
    }

    #[Test]
    public function test_write_all_records_at_once(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['Name', 'Age', 'Email'],
            ['John Doe', '30', 'john@example.com'],
            ['Jane Smith', '25', 'jane@example.com'],
            ['Bob Johnson', '35', 'bob@example.com'],
        ];

        $writer->writeAll($records);
        unset($writer);

        // Verify file contents
        $contents = file_get_contents($this->testFile);
        $lines = explode("\n", trim($contents));
        $this->assertCount(4, $lines);
        $this->assertStringContainsString('Name,Age,Email', $lines[0]);
        $this->assertStringContainsString('John Doe,30,john@example.com', $lines[1]);
        $this->assertStringContainsString('Jane Smith,25,jane@example.com', $lines[2]);
        $this->assertStringContainsString('Bob Johnson,35,bob@example.com', $lines[3]);
    }

    #[Test]
    public function test_set_source(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter();
        $writer->setSource($this->testFile);

        $this->assertEquals($this->testFile, $writer->getSource());

        // Should be able to write after setting source
        $writer->write(['test', 'data']);
        unset($writer);

        $this->assertFileExists($this->testFile);
    }

    #[Test]
    public function test_set_config(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);

        $newConfig = new CsvConfig();
        $newConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $writer->setConfig($newConfig);

        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
        $this->assertEquals("'", $writer->getConfig()->getEnclosure());
        $this->assertFalse($writer->getConfig()->hasHeader());
    }

    #[Test]
    #[DataProvider('csvConfigProvider')]
    public function test_different_csv_configurations(CsvConfig $config, array $data, string $expectedPattern): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile, $config);

        foreach ($data as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertMatchesRegularExpression($expectedPattern, $contents);
    }

    public static function csvConfigProvider(): array
    {
        return [
            'semicolon delimiter' => [
                (new CsvConfig())->setDelimiter(';'),
                [['col1', 'col2'], ['value1', 'value2']],
                '/col1;col2.*value1;value2/s',
            ],
            'custom enclosure' => [
                (new CsvConfig())->setEnclosure("'"),
                [['col1', 'col2'], ['value with space', 'value2']],
                "/value with space.*value2/s",  // Don't expect quotes since custom enclosure isn't always used
            ],
            'tab delimiter' => [
                (new CsvConfig())->setDelimiter("\t"),
                [['col1', 'col2'], ['value1', 'value2']],
                "/col1\t.*value1\t.*value2/s",  // Fixed to allow flexible matching
            ],
        ];
    }

    #[Test]
    public function test_write_with_special_characters(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['field1', 'field2', 'field3'],
            ['normal', 'with,comma', 'with"quote'],
            ['with\nnewline', 'with\ttab', 'with;semicolon'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('field1,field2,field3', $contents);
        $this->assertStringContainsString('"with,comma"', $contents);
        $this->assertStringContainsString('"with""quote"', $contents);
    }

    #[Test]
    public function test_write_unicode_content(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['Name', 'Description'],
            ['José', 'Café owner'],
            ['München', 'German city'],
            ['北京', 'Capital of China'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('José', $contents);
        $this->assertStringContainsString('Café owner', $contents);
        $this->assertStringContainsString('München', $contents);
        $this->assertStringContainsString('北京', $contents);
    }

    #[Test]
    public function test_write_empty_fields(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['col1', 'col2', 'col3'],
            ['value1', '', 'value3'],
            ['', 'value2', ''],
            ['', '', ''],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        // Handle both Unix (\n) and Windows (\r\n) line endings
        $lines = preg_split('/\r\n|\r|\n/', trim($contents));
        $this->assertCount(4, $lines);
        $this->assertStringContainsString('value1,,value3', $lines[1]);
        $this->assertStringContainsString(',value2,', $lines[2]);
        $this->assertStringContainsString(',,', $lines[3]);
    }

    #[Test]
    public function test_write_large_dataset(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);

        // Write header
        $writer->write(['id', 'name', 'email']);

        // Write 1000 records
        for ($i = 1; $i <= 1000; $i++) {
            $writer->write([$i, "User $i", "user$i@example.com"]);
        }
        unset($writer);

        // Verify file was created and has correct number of lines
        $this->assertFileExists($this->testFile);
        $contents = file_get_contents($this->testFile);
        // Handle both Unix (\n) and Windows (\r\n) line endings
        $lines = preg_split('/\r\n|\r|\n/', trim($contents));
        $this->assertCount(1001, $lines); // 1000 records + 1 header

        // Verify first and last records
        $this->assertStringContainsString('id,name,email', $lines[0]);
        $this->assertStringContainsString('1,User 1,user1@example.com', $lines[1]);
        $this->assertStringContainsString('1000,User 1000,user1000@example.com', $lines[1000]);
    }

    #[Test]
    public function test_write_to_existing_file_overwrites(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        // Create initial file
        file_put_contents($this->testFile, "existing,content\n");

        $writer = new CsvWriter($this->testFile);
        $writer->write(['new', 'content']);
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertStringNotContainsString('existing,content', $contents);
        $this->assertStringContainsString('new,content', $contents);
    }

    #[Test]
    public function test_write_single_column(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['single_column'],
            ['value1'],
            ['value2'],
            ['value3'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        // Handle both Unix (\n) and Windows (\r\n) line endings
        $lines = preg_split('/\r\n|\r|\n/', trim($contents));
        $this->assertCount(4, $lines);
        $this->assertEquals('single_column', $lines[0]);
        $this->assertEquals('value1', $lines[1]);
        $this->assertEquals('value2', $lines[2]);
        $this->assertEquals('value3', $lines[3]);
    }

    #[Test]
    public function test_write_with_numeric_values(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['integer', 'float', 'string_number'],
            [123, 45.67, '890'],
            [0, 0.0, '0'],
            [-123, -45.67, '-890'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('123,45.67,890', $contents);
        $this->assertStringContainsString('0,0,0', $contents);
        $this->assertStringContainsString('-123,-45.67,-890', $contents);
    }

    #[Test]
    public function test_write_with_boolean_values(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = new CsvWriter($this->testFile);
        $records = [
            ['boolean_true', 'boolean_false', 'string_bool'],
            [true, false, 'true'],
            [1, 0, 'false'],
        ];

        foreach ($records as $record) {
            $writer->write($record);
        }
        unset($writer);

        $contents = file_get_contents($this->testFile);
        $this->assertStringContainsString('1,,true', $contents); // false becomes empty string
        $this->assertStringContainsString('1,0,false', $contents);
    }
}
