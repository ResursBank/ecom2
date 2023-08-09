<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Checkbox\Link;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of CheckboxDto object.
 */
class Checkbox extends Model
{
    /**
     * @param string $id A unique id.
     * @param string $label Description rendered next to the checkbox.
     * @param bool $checked Whether the checkbox is checked or not.
     * @param bool $required Whether its required to be checked.
     * @throws EmptyValueException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly ?bool $checked = null,
        public readonly ?bool $required = null,
        public readonly ?Link $link = null,
    ) {
    }
}
