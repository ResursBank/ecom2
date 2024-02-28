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
    public readonly string $logo;

    private bool $eventTr = false;

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
        return $this->payment->customer->deliveryAddress?->addressRow1;

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
     * Render widget components (kept in separate method, so it can be executed
     * from subclasses).
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     */
    protected function renderWidget(): void
    {
        $logo = file_get_contents(filename: __DIR__ . '/resurs.svg');
        $logo = str_replace(
            search: '<svg',
            replace: '<svg style="height:1.2em; float: right; width: auto;"',
            subject: $logo
        );

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
    }

    /**
     * Get TD element with inline CSS.
     */
    public function getTdEl(
        string $content,
        bool $isHeader = false
    ): string {
        return '<td style="padding:0.3em 0.5em;' . (
            $isHeader ?
                ' text-align:right; min-width:22ch; font-weight:bold; vertical-align:top;' :
                ' width:100%;'
        ) . '">' . ($isHeader ? Translator::translate(phraseId: $content) : $content) . '</td>';
    }

    /**
     * Get TR element containing two TD elements using this structure:
     *
     * <tr>
     *     <td>[TITLE]</td>
     *     <td>[CONTENT]</td>
     * </tr>
     */
    public function getTrEl(
        string $title,
        string $content
    ): string {
        return '<tr style="' . $this->getTrStyle() . '">' .
            $this->getTdEl(content: $title, isHeader: true) .
            $this->getTdEl(content: $content) . '</tr>';
    }

    /**
     * In the template we need to render some TR elements manually, we must ensure to keep odd/even background-color
     * intact, which is why this is separated to its method outside getTrEl()
     */
    public function getTrStyle(): string
    {
        $result = 'background-color: #' . ($this->eventTr ? '006464' : '009b96') . ';';

        $this->eventTr = !$this->eventTr;

        return $result;
    }

    /**
     * Assemble address data and separate with <br />
     */
    public function getAddressContent(): string
    {
        $data = [
            $this->getAddressRow1()
        ];

        $addressRow2 = $this->getAddressRow2();

        if ($addressRow2 !== '') {
            $data[] = $addressRow2;
        }

        $data[] = $this->getCity();

        $country = $this->getCountryCode();

        $data[] = $country . ($country !== '' ? ' - ' : '') . $this->getPostalCode();

        return implode(separator: '<br />', array: $data);
    }

    public function getTagNames(): array
    {
        $tagNames = [];

        preg_match_all(
            pattern: '/<([a-zA-Z0-9\-]+)\b[^>]*>/',
            subject: $this->content,
            matches: $tagNames
        );

        return array_unique($tagNames[1]);
    }
}
