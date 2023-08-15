<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Required;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;

use function in_array;

/**
 * Implementation of PaymentMethodDto object.
 */
class PaymentMethod extends Model
{
    /**
     * @param array $required This is actually an array of enum values, see
     * ECP-546, currently fixed using evaluateFields to convert data.
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly int $fee,
        public array $required,
        public readonly string $subtitle,
        public readonly array $descriptions,
        public readonly string $terms,
        public readonly LinkCollection $links
    ) {
        $this->evaluateRequired();
    }

    /**
     * Convert anonymous strings to enum correspondent for required.
     */
    private function evaluateRequired(): void
    {
        $data = [];

        foreach ($this->required as $field) {
            $data[] = in_array(
                needle: $field,
                haystack: Required::cases(),
                strict: true
            ) ? $field : Required::from(value: $field);
        }

        $this->required = $data;
    }
}
