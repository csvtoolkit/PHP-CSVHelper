<?php

namespace Phpcsv\CsvHelper\Contracts;

interface CsvConfigInterface
{
    public function getDelimiter(): string;

    public function setDelimiter(string $delimiter): self;

    public function getEnclosure(): string;

    public function setEnclosure(string $enclosure): self;

    public function getEscape(): string;

    public function setEscape(string $escape): self;

    public function getPath(): string;

    public function setPath(string $path): self;

    public function getOffset(): int;

    public function setOffset(int $offset): self;

    public function hasHeader(): bool;

    public function setHasHeader(bool $hasHeader): self;
}
