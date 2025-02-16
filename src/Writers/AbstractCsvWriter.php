<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvWriterInterface;
use SplFileObject;

abstract class AbstractCsvWriter implements CsvWriterInterface
{
    protected ?array $header = null;

    protected CsvConfigInterface $config;

    protected ?SplFileObject $writer = null;

    abstract public function __construct(
        ?string $target = null,
        ?CsvConfigInterface $config = null
    );

    abstract public function getWriter(): ?SplFileObject;

    abstract public function getConfig(): CsvConfigInterface;

    abstract public function write(array $data): void;

    abstract public function setTarget(string $target): void;

    abstract public function getTarget(): string;
}
