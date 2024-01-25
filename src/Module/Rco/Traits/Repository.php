<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Traits;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;

/**
 * Shared traits for RCO+ Repository classes.
 */
trait Repository
{
    /**
     * Centralised business logic to ensure type safety for all endpoint
     * implementations in this class.
     *
     * @throws IllegalTypeException
     */
    public static function validateCheckoutModel(
        Collection|Model $model
    ): Checkout {
        if (!$model instanceof Checkout) {
            throw new IllegalTypeException(
                message: 'Expected ' . Checkout::class . ', got ' . $model::class
            );
        }

        return $model;
    }
}
