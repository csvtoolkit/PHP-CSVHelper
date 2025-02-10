<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvWriterInterface;
use SplFileObject;

class AbstractCsvWriter implements CsvWriterInterface
{
    protected ?array $header = null;

    protected CsvConfigInterface $config;

    protected ?SplFileObject $writer = null;

    public function getWriter(): ?SplFileObject
    {
        return null;
    }

    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    public function write(array $data): void {}
}
