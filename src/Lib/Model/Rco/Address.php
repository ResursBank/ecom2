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
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;

/**
 * Implementation of AddressDto object.
 */
class Address extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 0, max: 80)] public readonly string $street = '',
        #[StringLength(
            min: 0,
            max: 80
        )] public readonly string $addressLine = '',
        #[StringLength(
            min: 0,
            max: 24
        )] public readonly string $postalCode = '',
        #[StringLength(min: 0, max: 80)] public readonly string $city = '',
        #[StringLength(min: 0, max: 280)] public readonly string $notes = '',
        public readonly CountryCode $countryCode = CountryCode::UNKNOWN
    ) {
        parent::__construct();
    }
}
