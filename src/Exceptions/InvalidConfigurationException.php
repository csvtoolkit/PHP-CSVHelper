<?php

namespace Phpcsv\CsvHelper\Exceptions;

use Exception;

/**
 * Exception thrown when CSV configuration is invalid.
 *
 * Used to indicate that the provided CSV configuration parameters
 * are not valid (e.g., invalid delimiter length, invalid characters, etc.).
 */
class InvalidConfigurationException extends Exception
{
}
