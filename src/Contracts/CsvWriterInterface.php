<?php

namespace Phpcsv\CsvHelper\Contracts;

use SplFileObject;

interface CsvWriterInterface
{
    public function getWriter(): ?SplFileObject;

    public function getConfig(): CsvConfigInterface;

    public function write(array $data): void;
}
