<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation as Original;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 * RCO Plus specific payment information widget.
 */
class PaymentInformation extends Original
{
    public readonly Checkout $checkout;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws FilesystemException
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat
    ) {
        $this->checkout = Repository::get(id: $this->paymentId);
        $this->renderWidget();
    }

    public function hasAddress(): bool
    {
        return $this->getCustomerAddress() !== null;
    }

    public function getCustomerAddress(): ?Address
    {
        return $this->checkout->customer->useSeparateDeliveryAddress() ?
            $this->checkout->customer->delivery?->address :
            $this->checkout->customer->billing?->address;
    }

    public function getAddressRow2(): string
    {
        return (string) $this->getCustomerAddress()?->addressLine;
    }

    public function getAddressRow1(): string
    {
        return (string) $this->getCustomerAddress()?->street;
    }

    public function getCity(): string
    {
        return (string) $this->getCustomerAddress()?->city;
    }

    public function getCountryCode(): string
    {
        return (string) $this->getCustomerAddress()?->countryCode?->value;
    }

    public function getPostalCode(): string
    {
        return (string) $this->getCustomerAddress()?->postalCode;
    }

    public function getStatus(): string
    {
        return (string) $this->checkout->payment?->status->type?->value;
    }

    public function getPaymentMethodName(): string
    {
        return (string) $this->checkout->paymentMethods?->methods->getMethodName(
            methodId: (string) $this->checkout->payment?->selection->methodId
        );
    }

    public function getCustomerName(): string
    {
        return (string) $this->checkout->customer->billing?->name;
    }

    public function getTelephone(): string
    {
        return (string) $this->checkout->customer->billing?->contact?->phone;
    }

    public function getEmail(): string
    {
        return (string) $this->checkout->customer->billing?->contact?->email;
    }

    public function getAuthorizedAmount(): float
    {
        return (float) $this->checkout->payment?->status->authorizedAmount;
    }

    public function getCapturedAmount(): float
    {
        return (float) $this->checkout->payment?->status->capturedAmount;
    }

    public function getRefundedAmount(): float
    {
        return (float) $this->checkout->payment?->status->refundedAmount;
    }
}
