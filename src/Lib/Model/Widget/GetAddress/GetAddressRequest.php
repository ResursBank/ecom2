<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Widget\GetAddress;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsSwedishSsnOrOrg;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Describes incoming data from a get address request.
 *
 * See Module\Customer\Widget\GetAddress for AJAX call definition.
 */
class GetAddressRequest extends Model
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @todo Add tests. See ECP-272
     */
    public function __construct(
        #[StringIsSwedishSsnOrOrg] public readonly string $govId,
        public readonly CustomerType $customerType
    ) {
        parent::__construct();
    }
}
