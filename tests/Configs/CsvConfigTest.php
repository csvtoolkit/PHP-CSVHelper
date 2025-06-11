<?php

namespace Tests\Configs;

use CsvToolkit\Configs\CsvConfig;
use CsvToolkit\Contracts\CsvConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvConfig::class)]
class CsvConfigTest extends TestCase
{
    #[Test]
    public function test_default_configuration(): void
    {
        $config = new CsvConfig();

        $this->assertInstanceOf(CsvConfigInterface::class, $config);
        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());
        $this->assertEquals('\\', $config->getEscape());
        $this->assertTrue($config->hasHeader());
    }

    #[Test]
    public function test_set_and_get_delimiter(): void
    {
        $config = new CsvConfig();

        $result = $config->setDelimiter(';');
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertEquals(';', $config->getDelimiter());
    }

    #[Test]
    public function test_set_delimiter_with_tab(): void
    {
        $config = new CsvConfig();

        $config->setDelimiter("\t");
        $this->assertEquals("\t", $config->getDelimiter());
    }

    #[Test]
    public function test_set_delimiter_with_pipe(): void
    {
        $config = new CsvConfig();

        $config->setDelimiter('|');
        $this->assertEquals('|', $config->getDelimiter());
    }

    #[Test]
    public function test_set_and_get_enclosure(): void
    {
        $config = new CsvConfig();

        $result = $config->setEnclosure("'");
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertEquals("'", $config->getEnclosure());
    }

    #[Test]
    public function test_set_enclosure_with_backtick(): void
    {
        $config = new CsvConfig();

        $config->setEnclosure('`');
        $this->assertEquals('`', $config->getEnclosure());
    }

    #[Test]
    public function test_set_and_get_escape(): void
    {
        $config = new CsvConfig();

        $result = $config->setEscape('/');
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertEquals('/', $config->getEscape());
    }

    #[Test]
    public function test_set_escape_with_backslash(): void
    {
        $config = new CsvConfig();

        $config->setEscape('\\\\');
        $this->assertEquals('\\\\', $config->getEscape());
    }

    #[Test]
    public function test_set_and_get_has_header(): void
    {
        $config = new CsvConfig();

        // Test setting to false
        $result = $config->setHasHeader(false);
        $this->assertSame($config, $result); // Test fluent interface
        $this->assertFalse($config->hasHeader());

        // Test setting back to true
        $config->setHasHeader(true);
        $this->assertTrue($config->hasHeader());
    }

    #[Test]
    public function test_fluent_interface_chaining(): void
    {
        $config = new CsvConfig();

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
    #[DataProvider('csvConfigurationsProvider')]
    public function test_various_configurations(
        string $delimiter,
        string $enclosure,
        string $escape,
        bool $hasHeader
    ): void {
        $config = new CsvConfig();

        $config
            ->setDelimiter($delimiter)
            ->setEnclosure($enclosure)
            ->setEscape($escape)
            ->setHasHeader($hasHeader);

        $this->assertEquals($delimiter, $config->getDelimiter());
        $this->assertEquals($enclosure, $config->getEnclosure());
        $this->assertEquals($escape, $config->getEscape());
        $this->assertEquals($hasHeader, $config->hasHeader());
    }

    public static function csvConfigurationsProvider(): array
    {
        return [
            'semicolon_single_quote' => [';', "'", '\\', true],
            'pipe_double_quote' => ['|', '"', '/', false],
            'tab_backtick' => ["\t", '`', '\\', true],
            'comma_no_enclosure' => [',', '', '\\', false],
            'space_delimiter' => [' ', '"', '\\', true],
            'colon_delimiter' => [':', '"', '\\', false],
        ];
    }

    #[Test]
    public function test_empty_string_configurations(): void
    {
        $config = new CsvConfig();

        // Test empty enclosure
        $config->setEnclosure('');
        $this->assertEquals('', $config->getEnclosure());

        // Test empty escape
        $config->setEscape('');
        $this->assertEquals('', $config->getEscape());
    }

    #[Test]
    public function test_special_character_configurations(): void
    {
        $config = new CsvConfig();

        // Test newline as delimiter (unusual but valid)
        $config->setDelimiter("\n");
        $this->assertEquals("\n", $config->getDelimiter());

        // Test carriage return
        $config->setDelimiter("\r");
        $this->assertEquals("\r", $config->getDelimiter());

        // Test null character
        $config->setDelimiter("\0");
        $this->assertEquals("\0", $config->getDelimiter());
    }

    #[Test]
    public function test_unicode_character_configurations(): void
    {
        $config = new CsvConfig();

        // Test Unicode delimiter
        $config->setDelimiter('§');
        $this->assertEquals('§', $config->getDelimiter());

        // Test Unicode enclosure
        $config->setEnclosure('«');
        $this->assertEquals('«', $config->getEnclosure());

        // Test Unicode escape
        $config->setEscape('¿');
        $this->assertEquals('¿', $config->getEscape());
    }

    #[Test]
    public function test_configuration_immutability_after_creation(): void
    {
        $config = new CsvConfig();

        // Set initial values
        $config->setDelimiter(';')->setEnclosure("'")->setHasHeader(false);

        // Create a new config and verify it doesn't affect the first
        $config2 = new CsvConfig();
        $config2->setDelimiter('|')->setEnclosure('`')->setHasHeader(true);

        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());
        $this->assertFalse($config->hasHeader());

        $this->assertEquals('|', $config2->getDelimiter());
        $this->assertEquals('`', $config2->getEnclosure());
        $this->assertTrue($config2->hasHeader());
    }

    #[Test]
    public function test_multiple_character_delimiter(): void
    {
        $config = new CsvConfig();

        // Test multi-character delimiter
        $config->setDelimiter('||');
        $this->assertEquals('||', $config->getDelimiter());

        $config->setDelimiter('::');
        $this->assertEquals('::', $config->getDelimiter());
    }

    #[Test]
    public function test_multiple_character_enclosure(): void
    {
        $config = new CsvConfig();

        // Test multi-character enclosure
        $config->setEnclosure('""');
        $this->assertEquals('""', $config->getEnclosure());

        $config->setEnclosure("''");
        $this->assertEquals("''", $config->getEnclosure());
    }

    #[Test]
    public function test_configuration_persistence(): void
    {
        $config = new CsvConfig();

        // Set configuration
        $config->setDelimiter(';')
               ->setEnclosure("'")
               ->setEscape('/')
               ->setHasHeader(false);

        // Verify configuration persists across multiple calls
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals(';', $config->getDelimiter());
            $this->assertEquals("'", $config->getEnclosure());
            $this->assertEquals('/', $config->getEscape());
            $this->assertFalse($config->hasHeader());
        }
    }

    #[Test]
    public function test_configuration_modification_after_use(): void
    {
        $config = new CsvConfig();

        // Initial configuration
        $config->setDelimiter(',')->setEnclosure('"');
        $this->assertEquals(',', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());

        // Modify configuration
        $config->setDelimiter(';')->setEnclosure("'");
        $this->assertEquals(';', $config->getDelimiter());
        $this->assertEquals("'", $config->getEnclosure());

        // Modify again
        $config->setDelimiter('|')->setEnclosure('`');
        $this->assertEquals('|', $config->getDelimiter());
        $this->assertEquals('`', $config->getEnclosure());
    }

    #[Test]
    public function test_boolean_header_configurations(): void
    {
        $config = new CsvConfig();

        // Test explicit true
        $config->setHasHeader(true);
        $this->assertTrue($config->hasHeader());

        // Test explicit false
        $config->setHasHeader(false);
        $this->assertFalse($config->hasHeader());

        // Test toggling
        $config->setHasHeader(true);
        $this->assertTrue($config->hasHeader());
        $config->setHasHeader(false);
        $this->assertFalse($config->hasHeader());
    }

    #[Test]
    public function test_csv_format_presets(): void
    {
        // Test standard CSV format
        $standardCsv = new CsvConfig();
        $standardCsv->setDelimiter(',')->setEnclosure('"')->setEscape('\\');

        $this->assertEquals(',', $standardCsv->getDelimiter());
        $this->assertEquals('"', $standardCsv->getEnclosure());
        $this->assertEquals('\\', $standardCsv->getEscape());

        // Test European CSV format (semicolon separated)
        $europeanCsv = new CsvConfig();
        $europeanCsv->setDelimiter(';')->setEnclosure('"')->setEscape('\\');

        $this->assertEquals(';', $europeanCsv->getDelimiter());
        $this->assertEquals('"', $europeanCsv->getEnclosure());
        $this->assertEquals('\\', $europeanCsv->getEscape());

        // Test TSV format (tab separated)
        $tsv = new CsvConfig();
        $tsv->setDelimiter("\t")->setEnclosure('"')->setEscape('\\');

        $this->assertEquals("\t", $tsv->getDelimiter());
        $this->assertEquals('"', $tsv->getEnclosure());
        $this->assertEquals('\\', $tsv->getEscape());
    }

    #[Test]
    public function test_config_edge_cases(): void
    {
        $config = new CsvConfig();

        // Test same character for delimiter and enclosure (unusual but valid)
        $config->setDelimiter('"')->setEnclosure('"');
        $this->assertEquals('"', $config->getDelimiter());
        $this->assertEquals('"', $config->getEnclosure());

        // Test same character for enclosure and escape
        $config->setEnclosure('\\')->setEscape('\\');
        $this->assertEquals('\\', $config->getEnclosure());
        $this->assertEquals('\\', $config->getEscape());

        // Test same character for all three
        $config->setDelimiter('|')->setEnclosure('|')->setEscape('|');
        $this->assertEquals('|', $config->getDelimiter());
        $this->assertEquals('|', $config->getEnclosure());
        $this->assertEquals('|', $config->getEscape());
    }
}
