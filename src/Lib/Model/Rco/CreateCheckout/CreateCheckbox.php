<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckbox\Link;

/**
 * Implementation of CreateCheckboxDto object.
 */
class CreateCheckbox extends Model
{
    /**
     * @param string $id A unique id.
     * @param string $label Description rendered next to the checkbox.
     * @param ?bool $checked Whether the checkbox is checked or not.
     * @param ?bool $required Whether its required to be checked.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $id,
        #[StringLength(max: 512)] public readonly string $label,
        public readonly ?bool $checked = null,
        public readonly ?bool $required = null,
        public readonly ?Link $link = null
    ) {
        parent::__construct();
    }
}
