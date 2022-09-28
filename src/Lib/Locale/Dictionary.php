<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

class Dictionary
{
    /**
     * @param string $en
     * @param string $sv
     */
    public function __construct(
        public readonly string $en,
        public readonly string $sv,
    ) {
    }
}
