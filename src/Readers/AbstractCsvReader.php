<?php

namespace Phpcsv\CsvHelper\Readers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvReaderInterface;
use SplFileObject;

class AbstractCsvReader implements CsvReaderInterface
{
    protected CsvConfigInterface $config;

    protected int $position = 0;

    protected ?array $header = null;

    protected ?SplFileObject $reader = null;

    protected ?int $recordCount = null;

    public function getReader(): ?SplFileObject
    {
        return $this->reader;
    }

    public function getConfig(): CsvConfigInterface
    {
        if (! isset($this->config)) {
            $this->config = new CsvConfig;
        }

        return $this->config;
    }

    public function getRecordCount(): ?int
    {
        return null;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function getCurrentPosition(): int
    {
        return $this->position;
    }

    public function getRecord(): false|array
    {
        return false;
    }

    public function getHeader(): false|array
    {
        return false;
    }

    public function close(): void
    {
        $this->reader = null;
    }
}
