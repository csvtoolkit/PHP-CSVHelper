<?php

namespace Phpcsv\CsvHelper\Readers;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvReaderInterface;
use SplFileObject;

abstract class AbstractCsvReader implements CsvReaderInterface
{
    protected CsvConfigInterface $config;

    protected int $position = 0;

    protected ?array $header = null;

    protected ?SplFileObject $reader = null;

    protected ?int $recordCount = null;

    abstract public function __construct(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    );

    abstract public function getReader(): ?SplFileObject;

    abstract public function getConfig(): CsvConfigInterface;

    abstract public function getRecordCount(): ?int;

    abstract public function rewind(): void;

    abstract public function getCurrentPosition(): int;

    abstract public function getRecord(): array|false;

    abstract public function getHeader(): array|false;

    abstract public function hasRecords(): bool;

    abstract public function setSource(string $source): void;

    abstract public function getSource(): string;

    abstract public function setConfig(CsvConfigInterface $config): void;
}
