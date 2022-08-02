<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Model;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines a Store resource collected from the API.
 */
class Store
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
     * @todo Validation of representative id.
     * @todo Charset validation of most values.
     * @todo Validation of national store id.
     * @todo Validation of trade name. Do not know if this can be empty.
     * @todo Validation of popular name. Do not know if this can be empty.
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
     * @throws EmptyValueException
     */
    private function validateId(): void
    {
        $this->stringValidation->notEmpty(value: $this->id);
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
