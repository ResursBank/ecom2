<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type as CustomerType;
use Resursbank\Ecom\Lib\Model\Rco\Customer\TypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\LinkCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;

use function is_string;

/**
 * Implementation of PaymentMethodDto object.
 */
class PaymentMethod extends Model
{
    /**
     * NOTE: $sortOrder is not supplied by the API, we assign this manually when
     * fetching a list of payment methods from the API, to ensure payment
     * methods are sorted accurately in various implementations.
     *
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $methodId,
        public readonly string $name,
        public readonly Type $type,
        public readonly int $fee,
        public readonly RequiredCollection $required,
        public readonly string $subtitle,
        public readonly array $descriptions,
        public readonly string $terms,
        public readonly LinkCollection $links,
        public readonly TypeCollection $customerTypes,
        public readonly int $minLimit,
        public readonly int $maxLimit,
        public int $sortOrder = 0,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateDescriptions();
    }

    public function enabledForB2b(): bool
    {
        return in_array(
            needle: CustomerType::B2B,
            haystack: $this->customerTypes->getData(),
            strict: true
        );
    }

    public function enabledForB2c(): bool
    {
        return in_array(
            needle: CustomerType::B2C,
            haystack: $this->customerTypes->getData(),
            strict: true
        );
    }

    /**
     * @throws IllegalTypeException
     */
    private function validateDescriptions(): void
    {
        $this->arrayValidation->isOfType(
            data: $this->descriptions,
            type: 'string',
            compareFn: static fn (mixed $value) => is_string(value: $value)
        );
    }
}
