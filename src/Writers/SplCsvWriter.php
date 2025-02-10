<?php

namespace Phpcsv\CsvHelper\Writers;

use Phpcsv\CsvHelper\Configs\CsvConfig;
use Phpcsv\CsvHelper\Contracts\CsvConfigInterface;
use SplFileObject;

/**
 * CSV Writer implementation using SplFileObject
 *
 * This class provides functionality to write CSV files using PHP's built-in SplFileObject.
 * It supports custom delimiters, enclosures, and escape characters.
 */
class SplCsvWriter extends AbstractCsvWriter
{
    /**
     * Gets or initializes the SplFileObject writer
     *
     * @return SplFileObject|null The configured file object
     */
    public function getWriter(): ?SplFileObject
    {
        if (! $this->writer instanceof SplFileObject) {
            $this->writer = new SplFileObject($this->config->getPath(), 'w');
            $this->writer->setFlags(SplFileObject::READ_CSV);
            $this->writer->setCsvControl(
                $this->config->getDelimiter(),
                $this->config->getEnclosure(),
                $this->config->getEscape()
            );
        }

        return $this->writer;
    }

    /**
     * Gets the CSV configuration object
     *
     * @return CsvConfigInterface The CSV configuration object
     */
    public function getConfig(): CsvConfigInterface
    {
        if (! isset($this->config)) {
            $this->config = new CsvConfig;
        }

        return $this->config;
    }

    /**
     * Writes a single record to the CSV file
     *
     * @param  array  $data  The record to write
     */
    public function write(array $data): void
    {
        /** @var SplFileObject $writer */
        $writer = $this->getWriter();
        $writer->fputcsv($data, $this->config->getDelimiter(), $this->config->getEnclosure(), $this->config->getEscape());
    }
}
