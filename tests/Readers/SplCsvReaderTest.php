<?php

namespace Tests\Readers;

use Faker\Factory as FakerFactory;
use Phpcsv\CsvHelper\Exceptions\EmptyFileException;
use Phpcsv\CsvHelper\Exceptions\FileNotFoundException;
use Phpcsv\CsvHelper\Readers\SplCsvReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(SplCsvReader::class)]
class SplCsvReaderTest extends TestCase
{
    protected array $data = [];

    protected string $filePath;

    protected function setUp(): void
    {
        parent::setUp();
        $faker = FakerFactory::create();
        $this->filePath = __DIR__.'/data/sample.csv';
        $file = fopen($this->filePath, 'w');

        fputcsv($file, ['name', 'score'], ',', '"', '\\');
        $this->data[] = ['name', 'score'];

        for ($i = 0; $i < 20; $i++) {
            $record = [$faker->name, $faker->numberBetween(1, 100)];
            fputcsv($file, $record, ',', '"', '\\');
            $this->data[] = $record;
        }

        fclose($file);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $filePath = __DIR__.'/data/sample.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        unset($this->data);

    }

    public function test_read_csv_can_count_sample_records(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath($this->filePath);
        $count = $csvReader->getRecordCount();
        $this->assertEquals(count($this->data) - 1, $count); // Subtract 1 for the header
    }

    #[group('slow')]
    public function test_read_csv_can_count_1m_records(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/random_data_1m.csv');
        $count = $csvReader->getRecordCount();
        $this->assertEquals(991441, $count);
    }

    public function test_read_csv_can_get_header(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/sample.csv');
        $header = $csvReader->getHeader();
        $this->assertEquals(['name', 'score'], $header);
    }

    public function test_read_csv_can_get_record(): void
    {
        $data = $this->data;
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/sample.csv');
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => 'name', '1' => 'score'], $record);
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => $data[1][0], '1' => $data[1][1]], $record);
        $record = $csvReader->getRecord();
        $this->assertEquals(['0' => $data[2][0], '1' => $data[2][1]], $record);
    }

    public function test_read_csv_can_get_current_position(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/sample.csv');
        $csvReader->getRecord();
        $csvReader->getRecord();
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(2, $position);
    }

    public function test_read_csv_can_rewind(): void
    {
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/sample.csv');
        $csvReader->getRecord();
        $csvReader->getRecord();
        $csvReader->rewind();
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(0, $position);
    }

    public function test_read_csv_can_seek(): void
    {
        $data = $this->data;
        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/sample.csv');
        $csvReader->rewind();
        $record = $csvReader->seek(3);
        $position = $csvReader->getCurrentPosition();
        $this->assertEquals(3, $position);
        $this->assertEquals(['0' => $data[2][0], '1' => $data[2][1]], $record);
    }

    public function test_read_csv_with_empty_file(): void
    {
        $this->expectException(EmptyFileException::class);
        $emptyFilePath = __DIR__.'/data/empty.csv';
        file_put_contents($emptyFilePath, '');

        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath($emptyFilePath);

        $this->assertEquals(0, $csvReader->getRecordCount());
        $this->assertEmpty($csvReader->getHeader());

        unlink($emptyFilePath);
    }

    public function test_read_csv_with_nonexistent_file(): void
    {
        $this->expectException(FileNotFoundException::class);

        $csvReader = new SplCsvReader;
        $csvReader->getConfig()->setPath(__DIR__.'/data/nonexistent.csv');
        $csvReader->getRecordCount();
    }
}
