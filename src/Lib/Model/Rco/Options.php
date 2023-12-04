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
 * Implementation of OptionsDto object.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Options extends Model
{
    /**
     * @param TypeCollection $enabledCustomerTypes Defaults to both active.
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly bool $renderCart,
        public readonly bool $calculateShipping,
        public readonly bool $lookupB2CAddress,
        public readonly bool $renderCartCode,
        public readonly bool $renderNotes,
        public readonly bool $allowDelayedAuthorization,
        // phpcs:ignore
        public readonly TypeCollection $enabledCustomerTypes = new TypeCollection(data: [Type::B2C, Type::B2B]),
        public readonly ?RequiredCollection $requiredFields = null
    ) {
        parent::__construct();
    }
}
