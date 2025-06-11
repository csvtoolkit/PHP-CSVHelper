<?php

namespace CsvToolkit\Actions;

use CsvToolkit\Contracts\CsvConfigInterface;
use CsvToolkit\Contracts\CsvReaderInterface;
use CsvToolkit\Factories\CsvFactory;

/**
 * Action for creating CSV readers.
 *
 * Automatically selects the best available implementation based on extension availability.
 */
class CsvReaderAction
{
    /**
     * Creates a CSV reader using the best available implementation.
     *
     * @param string|null $source Optional CSV file path
     * @param CsvConfigInterface|null $config Optional configuration object
     * @return CsvReaderInterface The reader instance
     */
    public static function create(
        ?string $source = null,
        ?CsvConfigInterface $config = null
    ): CsvReaderInterface {
        return CsvFactory::createReader($source, $config);
    }
}
