<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a sale/stock movement cannot be fulfilled because one or more
 * ingredient batches do not hold enough stock.
 *
 * Extends RuntimeException so existing broad `catch (\RuntimeException)`
 * guards keep working, while callers that care can catch this specific type
 * and map it to an HTTP 409 Conflict instead of a 500.
 */
class InsufficientStockException extends RuntimeException
{
}
