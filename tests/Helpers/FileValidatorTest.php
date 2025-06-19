<?php

namespace Tests\Helpers;

use CsvToolkit\Exceptions\CsvWriterException;
use CsvToolkit\Exceptions\DirectoryNotFoundException;
use CsvToolkit\Exceptions\EmptyFileException;
use CsvToolkit\Exceptions\FileNotFoundException;
use CsvToolkit\Exceptions\FileNotReadableException;
use CsvToolkit\Helpers\FileValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileValidator::class)]
class FileValidatorTest extends TestCase
{
    private const string TEST_DATA_DIR = __DIR__ . '/data';

    private string $readableFile;

    private string $emptyFile;

    private string $nonExistentFile;

    private string $unreadableFile;

    private string $writableFile;

    private string $unwritableDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestDirectory();
        $this->setupTestFiles();
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

    private function setupTestFiles(): void
    {
        $this->readableFile = self::TEST_DATA_DIR . '/readable.csv';
        $this->emptyFile = self::TEST_DATA_DIR . '/empty.csv';
        $this->nonExistentFile = self::TEST_DATA_DIR . '/nonexistent.csv';
        $this->unreadableFile = self::TEST_DATA_DIR . '/unreadable.csv';
        $this->writableFile = self::TEST_DATA_DIR . '/writable.csv';
        $this->unwritableDir = self::TEST_DATA_DIR . '/unwritable';

        file_put_contents($this->readableFile, "Name,Age\nJohn,30\nJane,25");
        touch($this->emptyFile);

        file_put_contents($this->unreadableFile, "test data");
        chmod($this->unreadableFile, 0o000);

        if (! is_dir($this->unwritableDir)) {
            mkdir($this->unwritableDir, 0o777, true);
            chmod($this->unwritableDir, 0o555);
        }
    }

    private function cleanupTestFiles(): void
    {
        $files = [
            $this->readableFile,
            $this->emptyFile,
            $this->unreadableFile,
            $this->writableFile,
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                chmod($file, 0o777);
                unlink($file);
            }
        }

        if (is_dir($this->unwritableDir)) {
            chmod($this->unwritableDir, 0o777);
            rmdir($this->unwritableDir);
        }
    }

    private function cleanupTestDirectory(): void
    {
        if (is_dir(self::TEST_DATA_DIR)) {
            rmdir(self::TEST_DATA_DIR);
        }
    }

    #[Test]
    public function test_validate_file_readable_with_valid_file(): void
    {
        $this->expectNotToPerformAssertions();
        FileValidator::validateFileReadable($this->readableFile);
    }

    #[Test]
    public function test_validate_file_readable_with_nonexistent_file(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File does not exist: ' . $this->nonExistentFile);

        FileValidator::validateFileReadable($this->nonExistentFile);
    }

    #[Test]
    public function test_validate_file_readable_with_empty_file(): void
    {
        $this->expectException(EmptyFileException::class);
        $this->expectExceptionMessage('File is empty: ' . $this->emptyFile);

        FileValidator::validateFileReadable($this->emptyFile);
    }

    #[Test]
    public function test_validate_file_readable_with_unreadable_file(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Running as root, cannot test unreadable files');
        }

        $this->expectException(FileNotReadableException::class);
        $this->expectExceptionMessage('File is not readable: ' . $this->unreadableFile);

        FileValidator::validateFileReadable($this->unreadableFile);
    }

    #[Test]
    public function test_validate_file_exists_with_existing_file(): void
    {
        $this->expectNotToPerformAssertions();
        FileValidator::validateFileExists($this->readableFile);
    }

    #[Test]
    public function test_validate_file_exists_with_empty_file(): void
    {
        $this->expectNotToPerformAssertions();
        FileValidator::validateFileExists($this->emptyFile);
    }

    #[Test]
    public function test_validate_file_exists_with_nonexistent_file(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File does not exist: ' . $this->nonExistentFile);

        FileValidator::validateFileExists($this->nonExistentFile);
    }

    #[Test]
    public function test_validate_file_exists_with_unreadable_file(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Running as root, cannot test unreadable files');
        }

        $this->expectException(FileNotReadableException::class);
        $this->expectExceptionMessage('File is not readable: ' . $this->unreadableFile);

        FileValidator::validateFileExists($this->unreadableFile);
    }

    #[Test]
    public function test_validate_file_writable_with_new_file(): void
    {
        $this->expectNotToPerformAssertions();
        FileValidator::validateFileWritable($this->writableFile);
    }

    #[Test]
    public function test_validate_file_writable_with_existing_writable_file(): void
    {
        file_put_contents($this->writableFile, 'test');

        $this->expectNotToPerformAssertions();
        FileValidator::validateFileWritable($this->writableFile);
    }

    #[Test]
    public function test_validate_file_writable_with_unwritable_directory(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Running as root, cannot test unwritable directories');
        }

        $fileInUnwritableDir = $this->unwritableDir . '/test.csv';

        $this->expectException(CsvWriterException::class);
        $this->expectExceptionMessage('Directory is not writable: ' . $this->unwritableDir);

        FileValidator::validateFileWritable($fileInUnwritableDir);
    }

    #[Test]
    public function test_validate_file_writable_with_nonexistent_directory(): void
    {
        $fileInNonexistentDir = self::TEST_DATA_DIR . '/nonexistent/test.csv';

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage('Directory does not exist: ' . self::TEST_DATA_DIR . '/nonexistent');

        FileValidator::validateFileWritable($fileInNonexistentDir);
    }

    #[Test]
    public function test_validate_file_readable_with_empty_path(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File does not exist: ');

        FileValidator::validateFileReadable('');
    }

    #[Test]
    public function test_validate_file_exists_with_empty_path(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File does not exist: ');

        FileValidator::validateFileExists('');
    }

    #[Test]
    public function test_validate_file_writable_with_empty_path(): void
    {
        $this->expectException(CsvWriterException::class);
        $this->expectExceptionMessage('Target file path is required');

        FileValidator::validateFileWritable('');
    }

    #[Test]
    public function test_validate_file_readable_with_directory_path(): void
    {
        $this->expectException(EmptyFileException::class);

        FileValidator::validateFileReadable(self::TEST_DATA_DIR);
    }

    #[Test]
    public function test_validate_file_exists_with_directory_path(): void
    {
        $this->expectNotToPerformAssertions();

        FileValidator::validateFileExists(self::TEST_DATA_DIR);
    }

    #[Test]
    public function test_validate_file_writable_with_nonexistent_directory_fails(): void
    {
        $newDir = self::TEST_DATA_DIR . '/new_directory';
        $fileInNewDir = $newDir . '/test.csv';

        $this->assertDirectoryDoesNotExist($newDir);

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage('Directory does not exist: ' . $newDir);

        FileValidator::validateFileWritable($fileInNewDir);
    }

    #[Test]
    public function test_validate_methods_with_special_characters_in_path(): void
    {
        $specialFile = self::TEST_DATA_DIR . '/special file with spaces.csv';
        file_put_contents($specialFile, "test");

        $this->expectNotToPerformAssertions();
        FileValidator::validateFileReadable($specialFile);
        FileValidator::validateFileExists($specialFile);
        FileValidator::validateFileWritable($specialFile);

        unlink($specialFile);
    }

    #[Test]
    public function test_validate_methods_with_unicode_in_path(): void
    {
        $unicodeFile = self::TEST_DATA_DIR . '/файл_тест.csv';
        file_put_contents($unicodeFile, "test");

        $this->expectNotToPerformAssertions();
        FileValidator::validateFileReadable($unicodeFile);
        FileValidator::validateFileExists($unicodeFile);
        FileValidator::validateFileWritable($unicodeFile);

        unlink($unicodeFile);
    }

    #[Test]
    public function test_validate_file_readable_with_large_file(): void
    {
        $largeFile = self::TEST_DATA_DIR . '/large.csv';
        $largeContent = str_repeat("Name,Age\nJohn,30\n", 1000);
        file_put_contents($largeFile, $largeContent);

        $this->expectNotToPerformAssertions();
        FileValidator::validateFileReadable($largeFile);

        unlink($largeFile);
    }

    #[Test]
    public function test_exception_messages_contain_correct_file_paths(): void
    {
        try {
            FileValidator::validateFileReadable($this->nonExistentFile);
            $this->fail('Expected FileNotFoundException was not thrown');
        } catch (FileNotFoundException $e) {
            $this->assertStringContainsString($this->nonExistentFile, $e->getMessage());
        }

        try {
            FileValidator::validateFileReadable($this->emptyFile);
            $this->fail('Expected EmptyFileException was not thrown');
        } catch (EmptyFileException $e) {
            $this->assertStringContainsString($this->emptyFile, $e->getMessage());
        }
    }

    #[Test]
    public function test_validate_file_writable_with_existing_readonly_file(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Running as root, cannot test readonly files');
        }

        $readonlyFile = self::TEST_DATA_DIR . '/readonly.csv';
        file_put_contents($readonlyFile, 'test');
        chmod($readonlyFile, 0o444);

        $this->expectNotToPerformAssertions();

        FileValidator::validateFileWritable($readonlyFile);

        chmod($readonlyFile, 0o777);
        unlink($readonlyFile);
    }

    #[Test]
    public function test_validator_handles_symlinks(): void
    {
        $target = self::TEST_DATA_DIR . '/symlink_target.csv';
        $symlink = self::TEST_DATA_DIR . '/symlink.csv';

        file_put_contents($target, "test data");

        if (function_exists('symlink')) {
            symlink($target, $symlink);

            $this->expectNotToPerformAssertions();
            FileValidator::validateFileReadable($symlink);
            FileValidator::validateFileExists($symlink);
            FileValidator::validateFileWritable($symlink);

            unlink($symlink);
        }

        unlink($target);
    }
}
