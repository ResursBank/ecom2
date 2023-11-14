<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\EnabledCustomerTypeCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;

/**
 * Implementation of OptionsDto object.
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Options extends Model
{
    /**
     * @param EnabledCustomerTypeCollection|null $enabledCustomerTypes Defaults to both active.
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly ?EnabledCustomerTypeCollection $enabledCustomerTypes = new EnabledCustomerTypeCollection(
            data: [Type::B2C, Type::B2B]
        ),
        public readonly ?bool $renderCart = null,
        public readonly ?bool $calculateShipping = null,
        public readonly ?bool $lookupB2CAddress = null,
        public readonly ?bool $renderCartCode = null,
        public readonly ?bool $renderNotes = null,
        public readonly ?RequiredCollection $requiredFields = null
    ) {
    }
}
