<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use Phpcsv\CsvHelper\Contracts\CsvWriterInterface;
use SplFileObject;

abstract class AbstractCsvWriter implements CsvWriterInterface
{
    protected ?array $header = null;

    protected CsvConfigInterface $config;

    protected ?SplFileObject $writer = null;

    public function __construct(
        ?string $target = null,
        ?CsvConfigInterface $config = null
    ) {
        $this->config = $config ?? new CsvConfig;

        if ($target !== null) {
            $this->setTarget($target);
        }
    }

    public function getWriter(): ?SplFileObject
    {
        return null;
    }

    public function getConfig(): CsvConfigInterface
    {
        return $this->config;
    }

    public function write(array $data): void {}

    public function setTarget(string $target): void
    {
        $this->config->setPath($target);
    }

    public function getTarget(): string
    {
        return $this->config->getPath();
    }
}
