<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Annuity;

use InvalidArgumentException;
use Resursbank\Ecom\Module\Module as CoreModule;

/**
 * Business logic to interact with Annuity entities and related functionality.
 */
class Module extends CoreModule
{
    /**
     * @param string $method
     * @return void
     */
    public function sync(
        string $method
    ): void {
        if ($method === '') {
            throw new InvalidArgumentException(
                message: 'method may not be empty.'
            );
        }
        // @TODO Implement business logic to sync annuity factors.
    }
}
