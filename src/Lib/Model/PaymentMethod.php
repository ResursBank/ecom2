<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethod as PaymentMethodInterface;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Campaign;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLinkCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type;

/**
 * Defines payment method entity.
 *
 * NOTE: All Exceptions from namespace Validation extends ValidationException.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PaymentMethod extends Model implements PaymentMethodInterface
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        #[StringIsUuid] public readonly string $id,
        #[StringNotEmpty] public readonly string $name,
        public readonly Type $type,
        #[FloatValue(min: 0)] public readonly float $minPurchaseLimit,
        #[FloatValue(min: 0)] public readonly float $maxPurchaseLimit,
        #[FloatValue(min: 0)] public readonly float $minApplicationLimit,
        #[FloatValue(min: 0)] public readonly float $maxApplicationLimit,
        public readonly LegalLinkCollection $legalLinks,
        public readonly bool $enabledForLegalCustomer,
        public readonly bool $enabledForNaturalCustomer,
        public readonly bool $priceSignagePossible,
        public readonly ?string $description = null,
        public readonly ?Campaign $campaign = null,
        public int $sortOrder = 0
    ) {
        parent::__construct();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMinLimit(): float
    {
        return $this->minPurchaseLimit;
    }

    public function getMaxLimit(): float
    {
        return $this->maxPurchaseLimit;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Checks if payment method is eligible for part payment
     */
    public function isPartPayment(): bool
    {
        return $this->type === Type::RESURS_PART_PAYMENT ||
            $this->type === Type::RESURS_REVOLVING_CREDIT ||
            $this->type === Type::RESURS_NEW_REVOLVING_CREDIT ||
            $this->type === Type::RESURS_NEW_CARD ||
            $this->type === Type::RESURS_CARD;
    }

    /**
     * Checks if payment method is an internal Resurs payment method (rather than one provided by an external partner)
     */
    public function isResursMethod(): bool
    {
        return str_starts_with(haystack: $this->type->name, needle: 'RESURS_');
    }

    public function enabledForB2b(): bool
    {
        return $this->enabledForLegalCustomer;
    }

    public function enabledForB2c(): bool
    {
        return $this->enabledForNaturalCustomer;
    }

    public function isInternal(): bool
    {
        return str_starts_with(
            haystack: $this->type->value,
            needle: 'RESURS_'
        ) && $this->type->value !== 'RESURS_ZERO';
    }

    public function getTypeValue(): string
    {
        return $this->type->value;
    }
}
