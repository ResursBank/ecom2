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
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Throwable;

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
        $this->payment = Repository::get(paymentId: $this->paymentId);
        $this->renderWidget();
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

    public function getCancelledAmount(): float
    {
        return (float) $this->payment->order?->canceledAmount;
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

    /**
     * @throws ConfigException
     */
    public function getPaymentIdLabel(): string
    {
        $result = 'ID';

        try {
            $result = Translator::translate(phraseId: 'payment-id');
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return $result;
    }

    /**
     * Render widget components (kept in separate method, so it can be executed
     * from subclasses).
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     */
    protected function renderWidget(): void
    {
        $logo = file_get_contents(filename: __DIR__ . '/resurs.svg');

        if (!$logo) {
            throw new EmptyValueException(
                message: 'Failed to load logo image data'
            );
        }

        /* @phpstan-ignore-next-line */
        $this->logo = $logo;
        /* @phpstan-ignore-next-line */
        $this->content = $this->render(
            file: __DIR__ . '/payment-information.phtml'
        );
        /* @phpstan-ignore-next-line */
        $this->css = $this->render(file: __DIR__ . '/payment-information.css');
    }
}
