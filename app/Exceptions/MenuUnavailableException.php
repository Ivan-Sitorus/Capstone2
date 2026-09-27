<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an order references a menu that is missing or no longer active
 * at the moment the order is persisted inside its transaction.
 */
class MenuUnavailableException extends RuntimeException
{
}
