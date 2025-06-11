<?php

namespace Phpcsv\CsvHelper\Contracts;

use FastCSVReader;
use SplFileObject;

interface CsvReaderInterface
{
    public function getReader(): SplFileObject|FastCSVReader|null;

    public function getConfig(): CsvConfigInterface;

    public function getRecordCount(): ?int;

    public function rewind(): void;

    public function getCurrentPosition(): int;

    public function getRecord(): array|string|false;

    public function getHeader(): string|false|array;

    public function hasRecords(): bool;

    public function setSource(string $source): void;

    public function getSource(): string;

    public function setConfig(CsvConfigInterface $config): void;
}
