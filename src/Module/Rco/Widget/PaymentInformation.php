<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation as Original;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\Rco\Repository;
use Throwable;

/**
 * RCO Plus specific payment information widget.
 */
class PaymentInformation extends Original
{
    /**
     * This is over-written by other implementations extending this class.
     */
    public const PAYMENT_ID_LABEL = 'checkout-id';

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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat,
        public readonly bool $renderLogo = true
    ) {
        $this->checkout = Repository::get(id: $this->paymentId);
        $this->renderWidget();
    }

    public function hasAddress(): bool
    {
        return $this->getCustomerRecipient()?->address !== null;
    }

    public function getCustomerRecipient(): ?Recipient
    {
        return $this->checkout->customer->useSeparateDeliveryAddress() ?
            $this->checkout->customer->delivery :
            $this->checkout->customer->billing;
    }

    public function getAddressRow2(): string
    {
        return (string) $this->getCustomerRecipient()?->address?->addressLine;
    }

    public function getAddressRow1(): string
    {
        return (string) $this->getCustomerRecipient()?->address?->street;
    }

    public function getCity(): string
    {
        return (string) $this->getCustomerRecipient()?->address?->city;
    }

    public function getCountryCode(): string
    {
        return (string) $this->getCustomerRecipient()?->address?->countryCode?->value;
    }

    public function getPostalCode(): string
    {
        return (string) $this->getCustomerRecipient()?->address?->postalCode;
    }

    public function getStatus(): string
    {
        return (string) $this->checkout->payment->status->type?->value;
    }

    public function getPaymentMethodName(): string
    {
        return (string) $this->checkout->payment->methods->getMethodName(
            methodId: (string) $this->checkout->payment->selection->methodId
        );
    }

    public function getCustomerName(): string
    {
        return (string) $this->getCustomerRecipient()?->name;
    }

    public function getTelephone(): string
    {
        return (string) $this->getCustomerRecipient()?->contact?->phone;
    }

    public function getEmail(): string
    {
        return (string) $this->getCustomerRecipient()?->contact?->email;
    }

    public function getAuthorizedAmount(): float
    {
        return (float) $this->checkout->payment->status->authorizedAmount;
    }

    public function getCapturedAmount(): float
    {
        return (float) $this->checkout->payment->status->capturedAmount;
    }

    public function getRefundedAmount(): float
    {
        return (float) $this->checkout->payment->status->refundedAmount;
    }

    public function getCancelledAmount(): float
    {
        return (float) $this->checkout->payment->status->cancelledAmount;
    }

    /**
     * @throws ConfigException
     */
    public function getPaymentIdLabel(): string
    {
        $result = 'ID';

        try {
            $result = Translator::translate(phraseId: 'checkout-id');
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return $result;
    }

    /**
     * Take supplied amount value and format with currency symbol etc.
     */
    public function getFormattedAmount(float $amount): string
    {
        return Price::format(
            value: $amount / 100,
            currencySymbol: $this->currencySymbol,
            currencyFormat: $this->currencyFormat
        );
    }
}
