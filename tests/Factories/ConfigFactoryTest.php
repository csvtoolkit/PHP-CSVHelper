<?php

namespace Tests\Factories;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Enums\Encoding;
use CsvToolkit\Factories\ConfigFactory;
use CsvToolkit\Helpers\ExtensionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigFactory::class)]
class ConfigFactoryTest extends TestCase
{
    #[Test]
    public function test_create_returns_csv_config_when_fastcsv_available(): void
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = ConfigFactory::create();
        $this->assertInstanceOf(CsvConfig::class, $config);
    }

    #[Test]
    public function test_create_returns_spl_config_when_fastcsv_not_available(): void
    {
        if (ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test fallback');
        }

        $config = ConfigFactory::create();
        $this->assertInstanceOf(SplConfig::class, $config);
    }

    #[Test]
    public function test_create_with_path_parameter(): void
    {
        $testPath = '/tmp/test.csv';
        $config = ConfigFactory::create($testPath);

        $this->assertEquals($testPath, $config->getPath());
        $this->assertTrue($config->hasHeader()); // default
    }

    #[Test]
    public function test_create_with_header_parameter(): void
    {
        $config = ConfigFactory::create(null, false);

        $this->assertFalse($config->hasHeader());
        $this->assertEquals('', $config->getPath()); // default
    }

    #[Test]
    public function test_create_with_both_parameters(): void
    {
        $testPath = '/tmp/test.csv';
        $config = ConfigFactory::create($testPath, false);

        $this->assertEquals($testPath, $config->getPath());
        $this->assertFalse($config->hasHeader());
    }

    #[Test]
    public function test_create_fast_csv_always_returns_csv_config(): void
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = ConfigFactory::createFastCsv();
        $this->assertInstanceOf(CsvConfig::class, $config);
    }

    #[Test]
    public function test_create_fast_csv_with_parameters(): void
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $testPath = '/tmp/test.csv';
        $config = ConfigFactory::createFastCsv($testPath, false);

        $this->assertInstanceOf(CsvConfig::class, $config);
        $this->assertEquals($testPath, $config->getPath());
        $this->assertFalse($config->hasHeader());
    }

    #[Test]
    public function test_create_spl_always_returns_spl_config(): void
    {
        $config = ConfigFactory::createSpl();
        $this->assertInstanceOf(SplConfig::class, $config);
    }

    #[Test]
    public function test_create_spl_with_parameters(): void
    {
        $testPath = '/tmp/test.csv';
        $config = ConfigFactory::createSpl($testPath, false);

        $this->assertInstanceOf(SplConfig::class, $config);
        $this->assertEquals($testPath, $config->getPath());
        $this->assertFalse($config->hasHeader());
    }

    #[Test]
    public function test_create_fast_csv_throws_when_extension_unavailable(): void
    {
        if (ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension is available, cannot test exception');
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FastCSV extension is not available');

        ConfigFactory::createFastCsv();
    }

    #[Test]
    public function test_factory_creates_fresh_instances(): void
    {
        $config1 = ConfigFactory::create();
        $config2 = ConfigFactory::create();

        $this->assertNotSame($config1, $config2);

        $config1->setDelimiter(';');
        $this->assertEquals(',', $config2->getDelimiter());
    }

    #[Test]
    public function test_csv_config_supports_configuration(): void
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = ConfigFactory::createFastCsv();

        // Test fluent interface
        $result = $config
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('/')
            ->setHasHeader(false)
            ->setEncoding(Encoding::UTF8);

        $this->assertSame($config, $result);
        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
        $this->assertFalse($config->hasHeader());
        $this->assertEquals(Encoding::UTF8, $config->getEncodingEnum());
    }

    #[Test]
    public function test_spl_config_supports_basic_configuration(): void
    {
        $config = ConfigFactory::createSpl();

        // Test fluent interface
        $result = $config
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('/')
            ->setHasHeader(false);

        $this->assertSame($config, $result);
        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
        $this->assertFalse($config->hasHeader());
    }

    #[Test]
    public function test_config_default_values(): void
    {
        $config = ConfigFactory::create();

        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());
        $this->assertEquals('\\', $config->getEscape());
        $this->assertTrue($config->hasHeader());
        $this->assertEquals('', $config->getPath());
        $this->assertEquals(0, $config->getOffset());
    }

    #[Test]
    public function test_config_with_special_characters(): void
    {
        $config = ConfigFactory::create();

        $config
            ->setDelimiter("\t")
            ->setEnclosure("`")
            ->setEscape("#");

        $this->assertEquals("\t", $config->getDelimiter());
        $this->assertEquals("`", $config->getEnclosure());
        $this->assertEquals("#", $config->getEscape());
    }

    #[Test]
    public function test_config_path_handling(): void
    {
        $testPaths = [
            '/tmp/test.csv',
            'relative/path.csv',
            'test with spaces.csv',
            '测试.csv', // Unicode filename
        ];

        foreach ($testPaths as $path) {
            $config = ConfigFactory::create($path);
            $this->assertEquals($path, $config->getPath());

            // Test setting path after creation
            $config2 = ConfigFactory::create();
            $config2->setPath($path);
            $this->assertEquals($path, $config2->getPath());
        }
    }

    #[Test]
    public function test_config_offset_handling(): void
    {
        $config = ConfigFactory::create();

        $this->assertEquals(0, $config->getOffset());

        $config->setOffset(5);
        $this->assertEquals(5, $config->getOffset());
    }

    #[Test]
    public function test_encoding_support_in_csv_config(): void
    {
        if (! ExtensionHelper::isFastCsvLoaded()) {
            $this->markTestSkipped('FastCSV extension not available');
        }

        $config = ConfigFactory::createFastCsv();

        // Test default encoding
        $this->assertEquals(Encoding::UTF8, $config->getEncodingEnum());

        // Test setting different encodings
        $encodings = [
            Encoding::UTF8,
            Encoding::UTF16LE,
            Encoding::ASCII,
            Encoding::LATIN1,
        ];

        foreach ($encodings as $encoding) {
            $config->setEncoding($encoding);
            $this->assertEquals($encoding, $config->getEncodingEnum());
        }
    }
}
