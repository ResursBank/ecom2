<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Models;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a Store resource collected from the API.
 */
class Store extends Model
{
    /**
     * @param string $id | API identifier.
     * @param int $nationalStoreId
     * @param string $countryCode
     * @param string $tradeName
     * @param string $popularName
     * @param string $representativeId
     * @param StringValidation $stringValidation
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalValueException
     * @todo nationalStoreId needs more validation.
     * @todo $countryCode validation to be replaced by Enum\Country when DataConverter supports enums.
     * @todo $id, can this be empty?
     * @todo $nationalStoreId, what is the actual value range? Specified as Int64, may accept negative values.
     * @todo $tradeName, are there any validation rules?
     * @todo $popularName, are there any validation rules?
     */
    public function __construct(
        public readonly string $id,
        public readonly int $nationalStoreId,
        public readonly string $countryCode,
        public readonly string $tradeName,
        public readonly string $popularName,
        public readonly string $representativeId,
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
        $this->validateId();
        $this->validateCountryCode();
    }

    /**
     * @throws EmptyValueException|IllegalValueException
     */
    private function validateId(): void
    {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->isUuid(value: $this->id);
    }

    /**
     * @throws EmptyValueException|IllegalCharsetException
     * @todo Add charset validation.
     */
    private function validateCountryCode(): void
    {
        $this->stringValidation->notEmpty(value: $this->countryCode);
        $this->stringValidation->matchRegex(
            value: $this->countryCode,
            pattern: '/^[A-Z]{2}$/'
        );
    }
}
