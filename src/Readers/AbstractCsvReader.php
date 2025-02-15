<?php

namespace Phpcsv\CsvHelper\Readers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
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

    public function __construct(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    ) {
        $this->config = $config ?? new CsvConfig;

        if ($source !== null) {
            $this->setSource($source);
        }
    }

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

    public function getRecord(): array|false
    {
        return false;
    }

    public function getHeader(): array|false
    {
        return false;
    }

    public function hasRecords(): bool
    {
        return false;
    }

    public function setSource(string $source): void
    {
        $this->getConfig()->setPath($source);
    }

    public function getSource(): string
    {
        return $this->config->getPath();
    }

    public function setConfig(CsvConfigInterface $config): void
    {
        $this->config = $config;
    }
}
