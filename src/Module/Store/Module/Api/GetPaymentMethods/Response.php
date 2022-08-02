<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\ResponseInterface;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;

/**
 * Response object from an API request to retrieve payment methods.
 */
class Response implements ResponseInterface
{
    /**
     * @param array $data
     * @param ArrayValidation $arrayValidation
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function __construct(
        private readonly array $data,
        private readonly ArrayValidation $arrayValidation = new ArrayValidation(),
    ) {
        $this->validate();
    }

    /**
     * NOTE: While we could have settled for a public property in our
     * constructor that would have negated a lot of tests since stubbed
     * objects cannot be initialized at the time of writing.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     * @throws IllegalValueException
     * @throws IllegalTypeException
     */
    public function validate(): bool
    {
        if ($this->data !== null) {
            $this->arrayValidation->isSequential(data: $this->data);
            $this->arrayValidation->isStdClassCollection(data: $this->data);
        }

        return true;
    }
}
