<?php

namespace CsvToolkit\Actions;

use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Contracts\CsvWriterInterface;
use CsvToolkit\Factories\CsvFactory;

/**
 * Action for creating CSV writers.
 *
 * Automatically selects the best available implementation based on extension availability.
 */
class CsvWriterAction
{
    /**
     * Creates a CSV writer using the best available implementation.
     *
     * @param string|null $target Optional output file path
     * @param CsvConfigInterface|null $config Optional configuration object
     * @param array|null $headers Optional header row
     * @return CsvWriterInterface The writer instance
     */
    public static function create(
        ?string $target = null,
        ?CsvConfigInterface $config = null,
        ?array $headers = null
    ): CsvWriterInterface {
        return CsvFactory::createWriter($target, $config, $headers);
    }
}
