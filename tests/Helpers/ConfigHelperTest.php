<?php

namespace Tests\Helpers;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Enums\Encoding;
use CsvToolkit\Helpers\ConfigHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigHelper::class)]
class ConfigHelperTest extends TestCase
{
    #[Test]
    public function test_ensure_fast_csv_config_returns_csv_config_unchanged(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $result = ConfigHelper::ensureFastCsvConfig($csvConfig);

        $this->assertSame($csvConfig, $result);
        $this->assertEquals(';', $result->getDelimiter());
        $this->assertEquals("'", $result->getEnclosure());
        $this->assertFalse($result->hasHeader());
    }

    #[Test]
    public function test_ensure_fast_csv_config_converts_spl_config(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';')
                  ->setEnclosure("'")
                  ->setEscape('/')
                  ->setHasHeader(false)
                  ->setEncoding(Encoding::UTF16LE);

        $result = ConfigHelper::ensureFastCsvConfig($splConfig);

        $this->assertInstanceOf(CsvConfig::class, $result);
        $this->assertNotSame($splConfig, $result);
        $this->assertEquals(';', $result->getDelimiter());
        $this->assertEquals("'", $result->getEnclosure());
        $this->assertEquals('/', $result->getEscape());
        $this->assertFalse($result->hasHeader());
    }

    #[Test]
    public function test_ensure_spl_config_returns_spl_config_unchanged(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter(';')
                  ->setEnclosure("'")
                  ->setHasHeader(false)
                  ->setEncoding(Encoding::UTF16LE);

        $result = ConfigHelper::ensureSplConfig($splConfig);

        $this->assertSame($splConfig, $result);
        $this->assertEquals(';', $result->getDelimiter());
        $this->assertEquals("'", $result->getEnclosure());
        $this->assertFalse($result->hasHeader());
        $this->assertEquals(Encoding::UTF16LE, $result->getEncoding());
    }

    #[Test]
    public function test_ensure_spl_config_converts_csv_config(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(';')
                  ->setEnclosure("'")
                  ->setEscape('/')
                  ->setHasHeader(false);

        $result = ConfigHelper::ensureSplConfig($csvConfig);

        $this->assertInstanceOf(SplConfig::class, $result);
        $this->assertNotSame($csvConfig, $result);
        $this->assertEquals(';', $result->getDelimiter());
        $this->assertEquals("'", $result->getEnclosure());
        $this->assertEquals('/', $result->getEscape());
        $this->assertFalse($result->hasHeader());
        $this->assertEquals(Encoding::UTF8, $result->getEncoding());
    }

    #[Test]
    public function test_convert_to_spl_config_with_csv_config(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter("\t")
                  ->setEnclosure('`')
                  ->setEscape('#')
                  ->setHasHeader(true);

        $result = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertInstanceOf(SplConfig::class, $result);
        $this->assertEquals("\t", $result->getDelimiter());
        $this->assertEquals('`', $result->getEnclosure());
        $this->assertEquals('#', $result->getEscape());
        $this->assertTrue($result->hasHeader());
        $this->assertEquals(Encoding::UTF8, $result->getEncoding());
    }

    #[Test]
    public function test_convert_to_spl_config_with_spl_config_returns_same(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter("\t")
                  ->setEnclosure('`')
                  ->setEscape('#')
                  ->setHasHeader(true)
                  ->setEncoding(Encoding::UTF32LE);

        $result = ConfigHelper::convertToSplConfig($splConfig);

        $this->assertSame($splConfig, $result);
        $this->assertEquals("\t", $result->getDelimiter());
        $this->assertEquals('`', $result->getEnclosure());
        $this->assertEquals('#', $result->getEscape());
        $this->assertTrue($result->hasHeader());
        $this->assertEquals(Encoding::UTF32LE, $result->getEncoding());
    }

    #[Test]
    public function test_conversion_preserves_all_properties(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter('|')
                  ->setEnclosure('"')
                  ->setEscape('\\')
                  ->setHasHeader(false);

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals('|', $splConfig->getDelimiter());
        $this->assertEquals('"', $splConfig->getEnclosure());
        $this->assertEquals('\\', $splConfig->getEscape());
        $this->assertFalse($splConfig->hasHeader());
    }

    #[Test]
    public function test_conversion_with_empty_strings(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter('')
                  ->setEnclosure('')
                  ->setEscape('')
                  ->setHasHeader(true);

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals('', $splConfig->getDelimiter());
        $this->assertEquals('', $splConfig->getEnclosure());
        $this->assertEquals('', $splConfig->getEscape());
        $this->assertTrue($splConfig->hasHeader());
    }

    #[Test]
    public function test_conversion_with_special_characters(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter("\t")
                  ->setEnclosure("\n")
                  ->setEscape("\r")
                  ->setHasHeader(false);

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals("\t", $splConfig->getDelimiter());
        $this->assertEquals("\n", $splConfig->getEnclosure());
        $this->assertEquals("\r", $splConfig->getEscape());
        $this->assertFalse($splConfig->hasHeader());
    }

    #[Test]
    public function test_conversion_with_unicode_characters(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter('§')
                  ->setEnclosure('«')
                  ->setEscape('»')
                  ->setHasHeader(true);

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals('§', $splConfig->getDelimiter());
        $this->assertEquals('«', $splConfig->getEnclosure());
        $this->assertEquals('»', $splConfig->getEscape());
        $this->assertTrue($splConfig->hasHeader());
    }

    #[Test]
    public function test_ensure_fast_csv_config_with_all_properties(): void
    {
        $splConfig = new SplConfig();
        $splConfig->setDelimiter('|')
                  ->setEnclosure('`')
                  ->setEscape('#')
                  ->setHasHeader(true)
                  ->setEncoding(Encoding::UTF32LE);

        $result = ConfigHelper::ensureFastCsvConfig($splConfig);

        $this->assertInstanceOf(CsvConfig::class, $result);
        $this->assertEquals('|', $result->getDelimiter());
        $this->assertEquals('`', $result->getEnclosure());
        $this->assertEquals('#', $result->getEscape());
        $this->assertTrue($result->hasHeader());
    }

    #[Test]
    public function test_ensure_spl_config_sets_default_encoding(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(',')->setEnclosure('"');

        $result = ConfigHelper::ensureSplConfig($csvConfig);

        $this->assertInstanceOf(SplConfig::class, $result);
        $this->assertEquals(Encoding::UTF8, $result->getEncoding());
    }

    #[Test]
    public function test_conversion_maintains_independence(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(',');

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);
        $splConfig->setDelimiter(';');

        $this->assertEquals(',', $csvConfig->getDelimiter());
        $this->assertEquals(';', $splConfig->getDelimiter());
    }

    #[Test]
    public function test_helper_methods_handle_default_configs(): void
    {
        $defaultCsvConfig = new CsvConfig();
        $defaultSplConfig = new SplConfig();

        $convertedSpl = ConfigHelper::ensureSplConfig($defaultCsvConfig);
        $convertedCsv = ConfigHelper::ensureFastCsvConfig($defaultSplConfig);

        $this->assertEquals(',', $convertedSpl->getDelimiter());
        $this->assertEquals('"', $convertedSpl->getEnclosure());
        $this->assertEquals('\\', $convertedSpl->getEscape());
        $this->assertTrue($convertedSpl->hasHeader());
        $this->assertEquals(Encoding::UTF8, $convertedSpl->getEncoding());

        $this->assertEquals(',', $convertedCsv->getDelimiter());
        $this->assertEquals('"', $convertedCsv->getEnclosure());
        $this->assertEquals('\\', $convertedCsv->getEscape());
        $this->assertTrue($convertedCsv->hasHeader());
    }

    #[Test]
    public function test_multiple_conversions_are_consistent(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        $splConfig1 = ConfigHelper::convertToSplConfig($csvConfig);
        $splConfig2 = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals($splConfig1->getDelimiter(), $splConfig2->getDelimiter());
        $this->assertEquals($splConfig1->getEnclosure(), $splConfig2->getEnclosure());
        $this->assertEquals($splConfig1->getEscape(), $splConfig2->getEscape());
        $this->assertEquals($splConfig1->hasHeader(), $splConfig2->hasHeader());
        $this->assertEquals($splConfig1->getEncoding(), $splConfig2->getEncoding());
    }

    #[Test]
    public function test_ensure_methods_preserve_object_references(): void
    {
        $csvConfig = new CsvConfig();
        $splConfig = new SplConfig();

        $ensuredCsv = ConfigHelper::ensureFastCsvConfig($csvConfig);
        $ensuredSpl = ConfigHelper::ensureSplConfig($splConfig);

        $this->assertSame($csvConfig, $ensuredCsv);
        $this->assertSame($splConfig, $ensuredSpl);
    }

    #[Test]
    public function test_conversion_edge_cases(): void
    {
        $csvConfig = new CsvConfig();
        $csvConfig->setDelimiter('\\')
                  ->setEnclosure('\\')
                  ->setEscape('\\')
                  ->setHasHeader(true);

        $splConfig = ConfigHelper::convertToSplConfig($csvConfig);

        $this->assertEquals('\\', $splConfig->getDelimiter());
        $this->assertEquals('\\', $splConfig->getEnclosure());
        $this->assertEquals('\\', $splConfig->getEscape());
        $this->assertTrue($splConfig->hasHeader());
    }
}
