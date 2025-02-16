<?php

namespace Phpcsv\CsvHelper\Configs;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;

abstract class AbstractCsvConfig implements CsvConfigInterface
{
    protected string $delimiter = ',';

    protected string $enclosure = '"';

    protected string $escape = '\\';

    protected string $path = '';

    protected int $offset = 0;

    protected bool $hasHeader = true;

    abstract public function getDelimiter(): string;

    abstract public function setDelimiter(string $delimiter): CsvConfigInterface;

    abstract public function getEnclosure(): string;

    abstract public function setEnclosure(string $enclosure): CsvConfigInterface;

    abstract public function getEscape(): string;

    abstract public function setEscape(string $escape): CsvConfigInterface;

    abstract public function getPath(): string;

    abstract public function setPath(string $path): CsvConfigInterface;

    abstract public function getOffset(): int;

    abstract public function setOffset(int $offset): CsvConfigInterface;

    abstract public function hasHeader(): bool;

    abstract public function setHasHeader(bool $hasHeader): CsvConfigInterface;
}
