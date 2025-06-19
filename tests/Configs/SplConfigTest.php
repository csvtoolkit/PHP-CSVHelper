<?php

namespace Tests\Configs;

use CsvToolkit\Configs\SplConfig;
use CsvToolkit\Enums\Encoding;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SplConfig::class)]
class SplConfigTest extends TestCase
{
    #[Test]
    public function test_default_configuration(): void
    {
        $config = new SplConfig();

        $this->assertInstanceOf(SplConfig::class, $config);
        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());
        $this->assertEquals('\\', $config->getEscape());
        $this->assertTrue($config->hasHeader());
        $this->assertEquals(Encoding::UTF8, $config->getEncoding());
    }

    #[Test]
    public function test_set_and_get_delimiter(): void
    {
        $config = new SplConfig();

        $result = $config->setDelimiter(';');
        $this->assertSame($config, $result);
        $this->assertEquals(';', $config->getDelimiter());
    }

    #[Test]
    public function test_set_delimiter_with_special_characters(): void
    {
        $config = new SplConfig();

        $config->setDelimiter("\t");
        $this->assertEquals("\t", $config->getDelimiter());

        $config->setDelimiter('|');
        $this->assertEquals('|', $config->getDelimiter());
    }

    #[Test]
    public function test_set_and_get_enclosure(): void
    {
        $config = new SplConfig();

        $result = $config->setEnclosure("'");
        $this->assertSame($config, $result);
        $this->assertEquals("'", $config->getEnclosure());
    }

    #[Test]
    public function test_set_and_get_escape(): void
    {
        $config = new SplConfig();

        $result = $config->setEscape('/');
        $this->assertSame($config, $result);
        $this->assertEquals('/', $config->getEscape());
    }

    #[Test]
    public function test_set_and_get_has_header(): void
    {
        $config = new SplConfig();

        $result = $config->setHasHeader(false);
        $this->assertSame($config, $result);
        $this->assertFalse($config->hasHeader());

        $config->setHasHeader(true);
        $this->assertTrue($config->hasHeader());
    }

    #[Test]
    public function test_set_and_get_encoding(): void
    {
        $config = new SplConfig();

        $result = $config->setEncoding(Encoding::UTF16LE);
        $this->assertSame($config, $result);
        $this->assertEquals(Encoding::UTF16LE, $config->getEncoding());
    }

    #[Test]
    public function test_fluent_interface_chaining(): void
    {
        $config = new SplConfig();

        $result = $config
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('/')
            ->setHasHeader(false)
            ->setEncoding(Encoding::LATIN1);

        $this->assertSame($config, $result);
        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
        $this->assertFalse($config->hasHeader());
        $this->assertEquals(Encoding::LATIN1, $config->getEncoding());
    }

    #[Test]
    #[DataProvider('csvConfigurationsProvider')]
    public function test_various_configurations(
        string $delimiter,
        string $enclosure,
        string $escape,
        bool $hasHeader,
        Encoding $encoding
    ): void {
        $config = new SplConfig();

        $config
            ->setDelimiter($delimiter)
            ->setEnclosure($enclosure)
            ->setEscape($escape)
            ->setHasHeader($hasHeader)
            ->setEncoding($encoding);

        $this->assertEquals($delimiter, $config->getDelimiter());
        $this->assertEquals($enclosure, $config->getEnclosure());
        $this->assertEquals($escape, $config->getEscape());
        $this->assertEquals($hasHeader, $config->hasHeader());
        $this->assertEquals($encoding, $config->getEncoding());
    }

    public static function csvConfigurationsProvider(): array
    {
        return [
            'semicolon_single_quote' => [';', "'", '\\', true, Encoding::UTF8],
            'pipe_double_quote' => ['|', '"', '/', false, Encoding::UTF16LE],
            'tab_backtick' => ["\t", '`', '\\', true, Encoding::LATIN1],
            'comma_no_enclosure' => [',', '', '\\', false, Encoding::UTF8],
            'space_delimiter' => [' ', '"', '\\', true, Encoding::UTF32LE],
        ];
    }

    #[Test]
    public function test_empty_string_configurations(): void
    {
        $config = new SplConfig();

        $config->setEnclosure('');
        $this->assertEquals('', $config->getEnclosure());

        $config->setEscape('');
        $this->assertEquals('', $config->getEscape());
    }

    #[Test]
    public function test_special_character_configurations(): void
    {
        $config = new SplConfig();

        $config->setDelimiter("\n");
        $this->assertEquals("\n", $config->getDelimiter());

        $config->setDelimiter("\r");
        $this->assertEquals("\r", $config->getDelimiter());

        $config->setDelimiter("\0");
        $this->assertEquals("\0", $config->getDelimiter());
    }

    #[Test]
    public function test_unicode_character_configurations(): void
    {
        $config = new SplConfig();

        $config->setDelimiter('§');
        $this->assertEquals('§', $config->getDelimiter());

        $config->setEnclosure('«');
        $this->assertEquals('«', $config->getEnclosure());
    }

    #[Test]
    public function test_configuration_immutability_between_instances(): void
    {
        $config1 = new SplConfig();
        $config1->setDelimiter(';');

        $config2 = new SplConfig();
        $this->assertEquals(',', $config2->getDelimiter());
    }

    #[Test]
    public function test_spl_specific_encoding_configurations(): void
    {
        $config = new SplConfig();

        $config->setEncoding(Encoding::UTF8);
        $this->assertEquals(Encoding::UTF8, $config->getEncoding());

        $config->setEncoding(Encoding::UTF16LE);
        $this->assertEquals(Encoding::UTF16LE, $config->getEncoding());

        $config->setEncoding(Encoding::LATIN1);
        $this->assertEquals(Encoding::LATIN1, $config->getEncoding());
    }

    #[Test]
    public function test_multiple_character_delimiter(): void
    {
        $config = new SplConfig();

        $config->setDelimiter('||');
        $this->assertEquals('||', $config->getDelimiter());

        $config->setDelimiter('::');
        $this->assertEquals('::', $config->getDelimiter());
    }

    #[Test]
    public function test_multiple_character_enclosure(): void
    {
        $config = new SplConfig();

        $config->setEnclosure("''");
        $this->assertEquals("''", $config->getEnclosure());

        $config->setEnclosure('""');
        $this->assertEquals('""', $config->getEnclosure());
    }

    #[Test]
    public function test_configuration_persistence(): void
    {
        $config = new SplConfig();

        $config
            ->setDelimiter(';')
            ->setEnclosure("'")
            ->setEscape('/')
            ->setHasHeader(false)
            ->setEncoding(Encoding::UTF16LE);

        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
        $this->assertFalse($config->hasHeader());
        $this->assertEquals(Encoding::UTF16LE, $config->getEncoding());

        $config->setDelimiter(',');
        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
    }

    #[Test]
    public function test_encoding_type_enum_values(): void
    {
        $config = new SplConfig();

        $config->setEncoding(Encoding::UTF8);
        $this->assertSame(Encoding::UTF8, $config->getEncodingEnum());

        $config->setEncoding(Encoding::UTF16LE);
        $this->assertSame(Encoding::UTF16LE, $config->getEncodingEnum());

        $config->setEncoding(Encoding::LATIN1);
        $this->assertSame(Encoding::LATIN1, $config->getEncodingEnum());

        $config->setEncoding(Encoding::UTF32LE);
        $this->assertSame(Encoding::UTF32LE, $config->getEncodingEnum());
    }

    #[Test]
    public function test_csv_format_presets(): void
    {
        $config = new SplConfig();

        $config->setDelimiter(',')->setEnclosure('"')->setEscape('\\');
        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());
        $this->assertEquals('\\', $config->getEscape());

        $config->setDelimiter(';')->setEnclosure("'")->setEscape('/');
        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertEquals('/', $config->getEscape());
    }

    #[Test]
    public function test_config_edge_cases(): void
    {
        $config = new SplConfig();

        $config->setDelimiter('\\');
        $this->assertEquals('\\', $config->getDelimiter());

        $config->setEnclosure('\\');
        $this->assertEquals('\\', $config->getEnclosure());

        $config->setEscape('\\');
        $this->assertEquals('\\', $config->getEscape());
    }
}
