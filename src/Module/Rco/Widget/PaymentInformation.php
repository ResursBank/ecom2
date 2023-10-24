<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Widget;

use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation as Original;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 *
 */
class PaymentInformation extends Original
{
    public ?Checkout $checkout = null;

    public function getCheckout(): Checkout
    {
        if ($this->checkout !== null) {
            return $this->checkout;
        }

        $this->checkout = Repository::get(id: $this->paymentId);

        return $this->checkout;
    }

    public function hasAddress(): bool
    {
        return $this->getCheckout()->customer->billing !== null;
    }

    public function getAddressRow2(): string
    {
        return (string) $this->getCheckout()->customer->billing?->address?->addressLine;
    }

    public function getAddressRow1(): string
    {
        return (string) $this->getCheckout()->customer->billing?->address?->street;
    }

    public function getCity(): string
    {
        return (string) $this->getCheckout()->customer->billing?->address?->city;
    }

    public function getCountryCode(): string
    {
        return (string) $this->getCheckout()->customer->billing?->address?->countryCode?->value;
    }

    public function getPostalCode(): string
    {
        return (string) $this->getCheckout()->customer->billing?->address?->postalCode;
    }

    public function getStatus(): string
    {
        return (string) $this->getCheckout()->payment?->status->status?->value;
    }

    public function getPaymentMethodName(): string
    {
        return (string) $this->getCheckout()->payment?->selection->methodId;
    }

    public function getCustomerName(): string
    {
        return (string) $this->getCheckout()->customer->billing?->name;
    }

    public function getTelephone(): string
    {
        return (string) $this->getCheckout()->customer->billing?->contact?->phone;
    }

    public function getEmail(): string
    {
        return (string) $this->getCheckout()->customer->billing?->contact?->email;
    }

    public function getAuthorizedAmount(): float
    {
        return (float) $this->getCheckout()->payment?->status->authorizedAmount;
    }

    public function getCapturedAmount(): float
    {
        return (float) $this->getCheckout()->payment?->status->capturedAmount;
    }

    public function getRefundedAmount(): float
    {
        return (float) (float) $this->getCheckout()->payment?->status->refundedAmount;
    }

    /**
     * Take supplied amount value and format with currency symbol etc.
     */
    public function getFormattedAmount(float $amount): string
    {
        return $this->currencyFormat === CurrencyFormat::SYMBOL_FIRST ?
            $this->currencySymbol . ' ' . intval(value: $amount) :
            intval(value: $amount) . ' ' . $this->currencySymbol;
    }
}
