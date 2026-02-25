<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

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
        public readonly string $govId,
        public readonly CustomerType $customerType,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateGovId();
        parent::__construct();
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function validateGovId(): void
    {
        $this->stringValidation->notEmpty(value: $this->govId);

        if ($this->customerType === CustomerType::NATURAL) {
            $this->stringValidation->isSwedishSsn(value: $this->govId);
        } else {
            $this->stringValidation->isSwedishOrg(value: $this->govId);
        }
    }
}
