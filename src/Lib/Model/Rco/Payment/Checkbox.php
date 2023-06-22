<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\Payment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * RCO+ checkbox renderer array model.
 */
class Checkbox extends Model
{
    /**
     * @param string $id A unique id.
     * @param string $label Description rendered next to the checkbox.
     * @param bool $checked Whether the checkbox is checked or not. Defaults to false.
     * @param bool $required er the checkbox must be checked in order to proceed to payment. Defaults to false.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly bool $checked = false,
        public readonly bool $required = false
    ) {
    }
}
