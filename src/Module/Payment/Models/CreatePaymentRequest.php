<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Application;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Customer;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\MetaData;
use Resursbank\Ecom\Module\Payment\Models\CreatePayment\Options;

/**
 * Payment model used in a POST /payments request.
 */
class CreatePaymentRequest extends Model
{
    /**
     * @param string $storeId
     * @param string $paymentMethodId
     * @param Order $order
     * @param Application|null $application
     * @param Customer|null $customer
     * @param MetaData|null $metaData
     * @param Options|null $options
     * @param StringValidation $stringValidation
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        public readonly Order $order,
        public readonly ?Application $application,
        public readonly ?Customer $customer,
        public readonly ?MetaData $metaData,
        public readonly ?Options $options,
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
        $this->validateStoreId();
        $this->validatePaymentMethodId();
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validateStoreId(): void
    {
        $this->stringValidation->isUuid($this->storeId);
    }

    /**
     * @return void
     * @throws IllegalValueException
     */
    private function validatePaymentMethodId(): void
    {
        $this->stringValidation->isUuid($this->paymentMethodId);
    }
}
