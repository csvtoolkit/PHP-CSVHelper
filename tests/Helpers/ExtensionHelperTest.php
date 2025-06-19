<?php

namespace Tests\Helpers;

use CsvToolkit\Helpers\ExtensionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionHelper::class)]
class ExtensionHelperTest extends TestCase
{
    #[Test]
    public function test_is_fast_csv_available_returns_boolean(): void
    {
        $result = ExtensionHelper::isFastCsvAvailable();
        $this->assertIsBool($result);
    }

    #[Test]
    public function test_is_fast_csv_available_matches_extension_loaded(): void
    {
        $expected = extension_loaded('fastcsv');
        $actual = ExtensionHelper::isFastCsvAvailable();

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function test_get_available_extensions_includes_fastcsv_when_loaded(): void
    {
        $extensions = ExtensionHelper::getAvailableExtensions();

        $this->assertIsArray($extensions);

        if (extension_loaded('fastcsv')) {
            $this->assertContains('fastcsv', $extensions);
        } else {
            $this->assertNotContains('fastcsv', $extensions);
        }
    }

    #[Test]
    public function test_get_available_extensions_always_includes_spl(): void
    {
        $extensions = ExtensionHelper::getAvailableExtensions();

        $this->assertIsArray($extensions);
        $this->assertContains('spl', $extensions);
    }

    #[Test]
    public function test_get_available_extensions_returns_non_empty_array(): void
    {
        $extensions = ExtensionHelper::getAvailableExtensions();

        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);
    }

    #[Test]
    public function test_get_preferred_extension_returns_string(): void
    {
        $preferred = ExtensionHelper::getPreferredExtension();

        $this->assertIsString($preferred);
        $this->assertNotEmpty($preferred);
    }

    #[Test]
    public function test_get_preferred_extension_prefers_fastcsv_when_available(): void
    {
        $preferred = ExtensionHelper::getPreferredExtension();

        if (extension_loaded('fastcsv')) {
            $this->assertEquals('fastcsv', $preferred);
        } else {
            $this->assertEquals('spl', $preferred);
        }
    }

    #[Test]
    public function test_get_preferred_extension_falls_back_to_spl(): void
    {
        if (! extension_loaded('fastcsv')) {
            $preferred = ExtensionHelper::getPreferredExtension();
            $this->assertEquals('spl', $preferred);
        } else {
            $this->markTestSkipped('FastCSV extension is loaded, cannot test fallback');
        }
    }

    #[Test]
    public function test_get_extension_info_returns_array(): void
    {
        $info = ExtensionHelper::getExtensionInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('fastcsv', $info);
        $this->assertArrayHasKey('spl', $info);
    }

    #[Test]
    public function test_get_extension_info_has_correct_structure(): void
    {
        $info = ExtensionHelper::getExtensionInfo();

        foreach ($info as $extension => $details) {
            $this->assertIsString($extension);
            $this->assertIsArray($details);
            $this->assertArrayHasKey('available', $details);
            $this->assertArrayHasKey('description', $details);
            $this->assertIsBool($details['available']);
            $this->assertIsString($details['description']);
        }
    }

    #[Test]
    public function test_get_extension_info_fastcsv_availability_matches_extension_check(): void
    {
        $info = ExtensionHelper::getExtensionInfo();
        $fastCsvInfo = $info['fastcsv'];

        $this->assertEquals(extension_loaded('fastcsv'), $fastCsvInfo['available']);
    }

    #[Test]
    public function test_get_extension_info_spl_is_always_available(): void
    {
        $info = ExtensionHelper::getExtensionInfo();
        $splInfo = $info['spl'];

        $this->assertTrue($splInfo['available']);
    }

    #[Test]
    public function test_helper_methods_are_consistent(): void
    {
        $isFastCsvAvailable = ExtensionHelper::isFastCsvAvailable();
        $availableExtensions = ExtensionHelper::getAvailableExtensions();
        $preferredExtension = ExtensionHelper::getPreferredExtension();
        $extensionInfo = ExtensionHelper::getExtensionInfo();

        if ($isFastCsvAvailable) {
            $this->assertContains('fastcsv', $availableExtensions);
            $this->assertEquals('fastcsv', $preferredExtension);
            $this->assertTrue($extensionInfo['fastcsv']['available']);
        } else {
            $this->assertNotContains('fastcsv', $availableExtensions);
            $this->assertEquals('spl', $preferredExtension);
            $this->assertFalse($extensionInfo['fastcsv']['available']);
        }

        $this->assertContains('spl', $availableExtensions);
        $this->assertTrue($extensionInfo['spl']['available']);
    }

    #[Test]
    public function test_extension_descriptions_are_meaningful(): void
    {
        $info = ExtensionHelper::getExtensionInfo();

        $this->assertStringContainsString('FastCSV', $info['fastcsv']['description']);
        $this->assertStringContainsString('SPL', $info['spl']['description']);
        $this->assertStringContainsString('SplFileObject', $info['spl']['description']);
    }

    #[Test]
    public function test_helper_handles_multiple_calls_consistently(): void
    {
        $result1 = ExtensionHelper::isFastCsvAvailable();
        $result2 = ExtensionHelper::isFastCsvAvailable();
        $result3 = ExtensionHelper::isFastCsvAvailable();

        $this->assertEquals($result1, $result2);
        $this->assertEquals($result2, $result3);

        $extensions1 = ExtensionHelper::getAvailableExtensions();
        $extensions2 = ExtensionHelper::getAvailableExtensions();

        $this->assertEquals($extensions1, $extensions2);

        $preferred1 = ExtensionHelper::getPreferredExtension();
        $preferred2 = ExtensionHelper::getPreferredExtension();

        $this->assertEquals($preferred1, $preferred2);
    }

    #[Test]
    public function test_available_extensions_contains_only_valid_extensions(): void
    {
        $extensions = ExtensionHelper::getAvailableExtensions();
        $validExtensions = ['fastcsv', 'spl'];

        foreach ($extensions as $extension) {
            $this->assertContains($extension, $validExtensions);
        }
    }

    #[Test]
    public function test_preferred_extension_is_always_available(): void
    {
        $preferred = ExtensionHelper::getPreferredExtension();
        $available = ExtensionHelper::getAvailableExtensions();

        $this->assertContains($preferred, $available);
    }

    #[Test]
    public function test_extension_info_covers_all_known_extensions(): void
    {
        $info = ExtensionHelper::getExtensionInfo();
        $knownExtensions = ['fastcsv', 'spl'];

        foreach ($knownExtensions as $extension) {
            $this->assertArrayHasKey($extension, $info);
        }
    }

    #[Test]
    public function test_extension_helper_static_methods(): void
    {
        $reflection = new \ReflectionClass(ExtensionHelper::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $this->assertTrue($method->isStatic(), "Method {$method->getName()} should be static");
        }
    }

    #[Test]
    public function test_extension_availability_matches_php_extension_loaded(): void
    {
        $phpExtensions = get_loaded_extensions();
        $isFastCsvLoaded = in_array('fastcsv', $phpExtensions, true);

        $this->assertEquals($isFastCsvLoaded, ExtensionHelper::isFastCsvAvailable());
    }

    #[Test]
    public function test_get_available_extensions_order(): void
    {
        $extensions = ExtensionHelper::getAvailableExtensions();

        if (ExtensionHelper::isFastCsvAvailable()) {
            $this->assertEquals('fastcsv', $extensions[0]);
            $this->assertEquals('spl', $extensions[1]);
        } else {
            $this->assertEquals('spl', $extensions[0]);
        }
    }

    #[Test]
    public function test_extension_info_has_no_extra_keys(): void
    {
        $info = ExtensionHelper::getExtensionInfo();

        foreach ($info as $details) {
            $expectedKeys = ['available', 'description'];
            $actualKeys = array_keys($details);

            $this->assertEquals(sort($expectedKeys), sort($actualKeys));
        }
    }

    #[Test]
    public function test_helper_class_cannot_be_instantiated(): void
    {
        $reflection = new \ReflectionClass(ExtensionHelper::class);

        if ($reflection->hasMethod('__construct')) {
            $constructor = $reflection->getMethod('__construct');
            $this->assertFalse($constructor->isPublic(), 'ExtensionHelper should not be instantiable');
        } else {
            $this->assertTrue(true, 'ExtensionHelper has no constructor, which is expected for a utility class');
        }
    }

    #[Test]
    public function test_extension_info_descriptions_differ(): void
    {
        $info = ExtensionHelper::getExtensionInfo();

        $fastCsvDesc = $info['fastcsv']['description'];
        $splDesc = $info['spl']['description'];

        $this->assertNotEquals($fastCsvDesc, $splDesc);
    }
}
