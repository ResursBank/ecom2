<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Customer\TypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;

/**
 * Implementation of CreateOptionsDto object.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class CreateOptions extends Model
{
    /**
     * @param TypeCollection|null $enabledCustomerTypes Defaults to both active.
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        // phpcs:ignore
        public readonly ?TypeCollection $enabledCustomerTypes = new TypeCollection(data: [Type::B2C, Type::B2B]),
        public readonly ?bool $renderCart = null,
        public readonly ?bool $calculateShipping = null,
        public readonly ?bool $lookupB2CAddress = null,
        public readonly ?bool $renderCartCode = null,
        public readonly ?bool $renderNotes = null,
        public readonly ?RequiredCollection $requiredFields = null,
        public readonly ?bool $allowDelayedAuthorization = null
    ) {
        parent::__construct();
    }
}
