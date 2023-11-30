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
 * Implementation of CreateAddressDto object.
 */
class CreateAddress extends Model
{
    /**
     * @param string|null $street Street name.
     * @param string|null $addressLine Extra address line, for c/o or other.
     * @param string|null $postalCode A valid post code.
     * @param string|null $city Name of the city.
     * @param string|null $notes Free text area for notes.
     * @param CountryCode|null $countryCode ISO 3166-1 Alpha-2 country code.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 0, max: 80)] public readonly ?string $street = null,
        #[StringLength(
            min: 0,
            max: 80
        )] public readonly ?string $addressLine = null,
        #[StringLength(
            min: 0,
            max: 24
        )] public readonly ?string $postalCode = null,
        #[StringLength(min: 0, max: 80)] public readonly ?string $city = null,
        #[StringLength(min: 0, max: 280)] public readonly ?string $notes = null,
        public readonly ?CountryCode $countryCode = null
    ) {
        parent::__construct();
    }
}
