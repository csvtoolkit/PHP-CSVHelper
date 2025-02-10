<?php

namespace Tests\Writers;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SplFileObject;

#[CoversClass(SplCsvWriter::class)]
class SplCsvWriterTest extends TestCase
{
    private SplCsvWriter $csvWriter;

    private string $testFilePath;

    protected function setUp(): void
    {
        $this->testFilePath = sys_get_temp_dir().'/test.csv';
        $this->csvWriter = new SplCsvWriter;
        $this->csvWriter->getConfig()->setPath($this->testFilePath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function test_get_config_returns_csv_config_interface_instance(): void
    {
        $config = $this->csvWriter->getConfig();
        $this->assertInstanceOf(CsvConfigInterface::class, $config);
    }

    public function test_get_writer_returns_spl_file_object_with_correct_settings(): void
    {
        $writer = $this->csvWriter->getWriter();
        $this->assertInstanceOf(SplFileObject::class, $writer);

        $this->assertEquals(',', $writer->getCsvControl()[0]);
        $this->assertEquals('"', $writer->getCsvControl()[1]);
        $this->assertEquals('\\', $writer->getCsvControl()[2]);
    }

    public function test_write_method_writes_data_to_csv_file(): void
    {
        $data = ['John', 'Doe', 'john.doe@example.com'];
        $this->csvWriter->write($data);

        $fileContent = file_get_contents($this->testFilePath);
        $this->assertStringContainsString(implode(',', $data), $fileContent);
    }
}
