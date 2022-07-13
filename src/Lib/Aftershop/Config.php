<?php

/** @noinspection SpellCheckingInspection */

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Aftershop;

/**
 * Configuration directives affecting After Shop methods.
 *
 * After Shop means methods to debit / annul / credit etc. payments at Resurs.
 */
class Config
{
    /**
     * @param bool $enabled
     */
    public function __construct(
        public readonly bool $enabled = false
    ) {
    }
}
