<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use function in_array;

/**
 * Implementation of OptionsDto object.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Options extends Model
{
    /**
     * @param array $requiredFields This is actually an array of enum values,
     * see ECP-543, currently fixed using evaluateRequiredFields to convert data.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly ?bool $b2bEnabled = null,
        public readonly ?bool $renderCart = null,
        public readonly ?bool $mutableCart = null,
        public readonly ?bool $calculateShipping = null,
        public readonly ?bool $lookupB2CAddress = null,
        public readonly ?bool $renderCartCode = null,
        public readonly ?bool $renderNotes = null,
        public array $requiredFields = [
            Required::EMAIL,
            Required::PHONE,
            Required::NAME,
            Required::ADDRESS
        ]
    ) {
        $this->evaluateRequiredFields();
    }

    /**
     * Convert anonymous strings to enum correspondent for requiredFields.
     */
    private function evaluateRequiredFields(): void
    {
        $data = [];

        foreach ($this->requiredFields as $field) {
            $data[] = in_array(
                needle: $field,
                haystack: Required::cases(),
                strict: true
            ) ? $field : Required::from(value: $field);
        }

        $this->requiredFields = $data;
    }
}
