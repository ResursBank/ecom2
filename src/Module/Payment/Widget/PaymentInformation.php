<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Widget;

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
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

/**
 * Renders Payment Information widget for use in admin panel order view
 *
 * @todo Refactor this file. Contains several null pointers, file_get_contents can return false, etc.
 */
class PaymentInformation extends Widget
{
    /** @var Payment */
    public readonly Payment $payment;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $logo;

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat
    ) {
        /* We extend this class from the RCO module, since we need the exact
           same widget for RCO, but our resources differ slightly (for example,
           the Payment object is available in MAPI but in RCO we instead have
           a Checkout object). We must avoid the code below from executing when
           using RCO, we should refactor this to remove the payment variable
           instead but this would introduce a breaking change. See */
        if (
            Config::getJwtAuth()->scope === Scope::MERCHANT_API ||
            Config::getJwtAuth()->scope === Scope::MOCK_MERCHANT_API
        ) {
            $this->payment = Repository::get(paymentId: $this->paymentId);
        }

        $logo = file_get_contents(filename: __DIR__ . '/resurs.svg');

        if (!$logo) {
            throw new EmptyValueException(
                message: 'Failed to load logo image data'
            );
        }

        $this->logo = $logo;
        $this->content = $this->render(
            file: __DIR__ . '/payment-information.phtml'
        );
        $this->css = $this->render(file: __DIR__ . '/payment-information.css');
    }

    /**
     * Fetches CSS without instantiating an object.
     *
     * @throws EmptyValueException
     */
    public static function getCss(): string
    {
        $css = file_get_contents(
            filename: __DIR__ . '/payment-information.css'
        );

        if (!$css) {
            throw new EmptyValueException(
                message: 'Failed to load stylesheet data'
            );
        }

        return $css;
    }

    /**
     * Fetch formatted delivery address.
     *
     * @deprecated Use methods to collect individual values instead.
     */
    public function getAddress(): string
    {
        if ($this->payment->customer->deliveryAddress) {
            return $this->payment->customer->deliveryAddress->addressRow1 . '<br />' . PHP_EOL .
                ($this->payment->customer->deliveryAddress->addressRow2 ?
                    $this->payment->customer->deliveryAddress->addressRow2 . '<br />' . PHP_EOL :
                    ''
                ) .
                $this->payment->customer->deliveryAddress->postalArea . '<br />' . PHP_EOL .
                ($this->payment->customer->deliveryAddress->countryCode !== null ?
                $this->payment->customer->deliveryAddress->countryCode->value . ' - ' : '') .
                $this->payment->customer->deliveryAddress->postalCode;
        }

        return '';
    }

    public function hasAddress(): bool
    {
        return $this->payment->customer->deliveryAddress !== null;
    }

    public function getAddressRow2(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->addressRow2;
    }

    public function getAddressRow1(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->addressRow1;
    }

    public function getCity(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->postalArea;
    }

    public function getCountryCode(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->countryCode?->value;
    }

    public function getPostalCode(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->postalCode;
    }

    public function getStatus(): string
    {
        return $this->payment->status->value;
    }

    public function getPaymentMethodName(): string
    {
        return (string) $this->payment->paymentMethod?->name;
    }

    public function getCustomerName(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->fullName;
    }

    public function getTelephone(): string
    {
        return $this->payment->customer->mobilePhone ?? '';
    }

    public function getEmail(): string
    {
        return $this->payment->customer->email ?? '';
    }

    public function getAuthorizedAmount(): float
    {
        return (float) $this->payment->order?->authorizedAmount;
    }

    public function getCapturedAmount(): float
    {
        return (float) $this->payment->order?->capturedAmount;
    }

    public function getRefundedAmount(): float
    {
        return (float) $this->payment->order?->refundedAmount;
    }

    /**
     * Take supplied amount value and format with currency symbol etc.
     */
    public function getFormattedAmount(float $amount): string
    {
        return $this->currencyFormat === CurrencyFormat::SYMBOL_FIRST ?
            $this->currencySymbol . ' ' . $amount :
            $amount . ' ' . $this->currencySymbol;
    }
}
