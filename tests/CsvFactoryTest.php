<?php

namespace Tests;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Factories\CsvFactory;
use CsvToolkit\Readers\CsvReader;
use CsvToolkit\Readers\SplCsvReader;
use CsvToolkit\Writers\CsvWriter;
use CsvToolkit\Writers\SplCsvWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvFactory::class)]
class CsvFactoryTest extends TestCase
{
    #[Test]
    public function test_create_reader_returns_interface(): void
    {
        $reader = CsvFactory::createReader();

        $this->assertInstanceOf(CsvReaderInterface::class, $reader);
    }

    #[Test]
    public function test_create_reader_with_fastcsv_extension(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $reader = CsvFactory::createReader();

        $this->assertInstanceOf(CsvReader::class, $reader);
    }

    #[Test]
    public function test_create_reader_without_fastcsv_extension(): void
    {
        if (extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension is loaded, cannot test fallback');
        }

        $reader = CsvFactory::createReader();

        $this->assertInstanceOf(SplCsvReader::class, $reader);
    }

    #[Test]
    public function test_create_reader_with_parameters(): void
    {
        $source = '/path/to/file.csv';
        $config = new CsvConfig();
        $config->setDelimiter(';');

        $reader = CsvFactory::createReader($source, $config);

        $this->assertInstanceOf(CsvReaderInterface::class, $reader);
        $this->assertEquals($source, $reader->getSource());
        $this->assertEquals(';', $reader->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_writer_returns_interface(): void
    {
        $writer = CsvFactory::createWriter();

        $this->assertInstanceOf(CsvWriterInterface::class, $writer);
    }

    #[Test]
    public function test_create_writer_with_fastcsv_extension(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $writer = CsvFactory::createWriter();

        $this->assertInstanceOf(CsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_writer_without_fastcsv_extension(): void
    {
        if (extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension is loaded, cannot test fallback');
        }

        $writer = CsvFactory::createWriter();

        $this->assertInstanceOf(SplCsvWriter::class, $writer);
    }

    #[Test]
    public function test_create_writer_with_parameters(): void
    {
        $target = '/path/to/output.csv';
        $config = new CsvConfig();
        $config->setDelimiter(';');
        $headers = ['Name', 'Age', 'Email'];

        $writer = CsvFactory::createWriter($target, $config, $headers);

        $this->assertInstanceOf(CsvWriterInterface::class, $writer);
        $this->assertEquals($target, $writer->getTarget());
        $this->assertEquals(';', $writer->getConfig()->getDelimiter());
    }

    #[Test]
    public function test_create_config(): void
    {
        $config = CsvFactory::createConfig();

        $this->assertInstanceOf(CsvConfigInterface::class, $config);
        $this->assertInstanceOf(CsvConfig::class, $config);
    }

    #[Test]
    public function test_is_fast_csv_available(): void
    {
        $isAvailable = CsvFactory::isFastCsvAvailable();

        $this->assertIsBool($isAvailable);
        $this->assertEquals(extension_loaded('fastcsv'), $isAvailable);
    }

    #[Test]
    public function test_get_implementation_info_with_fastcsv(): void
    {
        if (! extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension not loaded');
        }

        $info = CsvFactory::getImplementationInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('implementation', $info);
        $this->assertArrayHasKey('extension_loaded', $info);
        $this->assertArrayHasKey('version', $info);

        $this->assertEquals('FastCSV', $info['implementation']);
        $this->assertTrue($info['extension_loaded']);
        $this->assertIsString($info['version']);
    }

    #[Test]
    public function test_get_implementation_info_without_fastcsv(): void
    {
        if (extension_loaded('fastcsv')) {
            $this->markTestSkipped('FastCSV extension is loaded, cannot test fallback');
        }

        $info = CsvFactory::getImplementationInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('implementation', $info);
        $this->assertArrayHasKey('extension_loaded', $info);
        $this->assertArrayHasKey('version', $info);

        $this->assertEquals('SplFileObject', $info['implementation']);
        $this->assertFalse($info['extension_loaded']);
        $this->assertNull($info['version']);
    }

    #[Test]
    public function test_factory_methods_consistency(): void
    {
        // Both factory methods should use the same implementation choice
        $reader = CsvFactory::createReader();
        $writer = CsvFactory::createWriter();
        $isFastCsv = CsvFactory::isFastCsvAvailable();

        if ($isFastCsv) {
            $this->assertInstanceOf(CsvReader::class, $reader);
            $this->assertInstanceOf(CsvWriter::class, $writer);
        } else {
            $this->assertInstanceOf(SplCsvReader::class, $reader);
            $this->assertInstanceOf(SplCsvWriter::class, $writer);
        }
    }

    #[Test]
    public function test_factory_creates_working_instances(): void
    {
        $config = CsvFactory::createConfig();
        $config->setDelimiter(',')
               ->setEnclosure('"')
               ->setHasHeader(true);

        // Test that created instances work
        $reader = CsvFactory::createReader(null, $config);
        $writer = CsvFactory::createWriter(null, $config);

        $this->assertEquals(',', $reader->getConfig()->getDelimiter());
        $this->assertEquals('"', $reader->getConfig()->getEnclosure());
        $this->assertTrue($reader->getConfig()->hasHeader());

        $this->assertEquals(',', $writer->getConfig()->getDelimiter());
        $this->assertEquals('"', $writer->getConfig()->getEnclosure());
    }
}
