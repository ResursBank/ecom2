<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;

/**
 * Defines a PaymentMethod collection.
 */
class PaymentMethodCollection extends Collection
    implements Interface\PaymentMethodCollection
{
    /**
     * @param array<int, PaymentMethod> $data
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: PaymentMethod::class);
    }

    /**
     * @inheritDoc
     *
     * @param string $methodId
     * @return string
     */
    public function getMethodName(string $methodId): string
    {
        $result = $methodId;

        /** @var PaymentMethod $method */
        foreach ($this->getData() as $method) {
            if ($method->getId() === $methodId) {
                $result = $method->getName();
                break;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @param string $methodId
     * @return PaymentMethod
     * @throws MissingValueException
     */
    public function getById(string $methodId): PaymentMethod
    {
        /** @var \Resursbank\Ecom\Lib\Model\PaymentMethod $method */
        foreach ($this->getData() as $method) {
            if ($method->getId() === $methodId) {
                return $method;
            }
        }

        throw new MissingValueException(message: 'Method with id ' . $methodId .
            ' does not exist in collection.');
    }
}
