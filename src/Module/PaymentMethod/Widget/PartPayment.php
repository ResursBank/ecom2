<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type as LegalLinkType;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PriceSignage\Models\Cost;
use Resursbank\Ecom\Module\PriceSignage\Repository as SignageRepository;

/**
 * Renders Part payment widget HTML and CSS
 */
class PartPayment extends Widget
{
    /** @var string  */
    public readonly string $logo;

    /** @var string  */
    public readonly string $infoText;

    /** @var string  */
    public readonly string $content;

    /** @var string  */
    public readonly string $css;

    /** @var string  */
    public readonly string $readMore;

    /** @var string  */
    public readonly string $iframeUrl;

    /** @var string  */
    public readonly string $startingAt;

    /** @var string  */
    public readonly string $error;

    /** @var string  */
    public readonly string $js;

    /** @var Cost  */
    public readonly Cost $cost;

    /**
     * @param string $storeId
     * @param PaymentMethod $paymentMethod
     * @param int $months
     * @param float $amount
     * @param string $apiUrl
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     */
    public function __construct(
        private readonly string $storeId,
        private readonly PaymentMethod $paymentMethod,
        private readonly int $months,
        private readonly float $amount,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat,
        public readonly string $apiUrl
    ) {
        $this->cost = $this->getCost();
        $this->logo = file_get_contents(filename: __DIR__ . '/resurs.svg');
        $this->infoText = Translator::translate(phraseId: 'pay-in-installments-with-resurs-bank');
        $this->startingAt = $this->getStartingAt();
        $this->readMore = Translator::translate(phraseId: 'read-more');
        $this->iframeUrl = $this->getIframeUrl();
        $this->error = Translator::translate(phraseId: 'part-payment-general-error');

        $this->content = $this->render(file: __DIR__ . '/part-payment.phtml');
        $this->css = $this->render(file: __DIR__ . '/part-payment.css');
        $this->js = $this->render(file: __DIR__ . '/part-payment-js.phtml');
    }

    /**
     * Fetch a Cost object from the Price signage API
     *
     * @return Cost
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function getCost(): Cost
    {
        $costs = SignageRepository::getPriceSignage(
            storeId: $this->storeId,
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->amount,
            monthFilter: $this->months
        );

        if (empty($costs->costList->toArray())) {
            throw new EmptyValueException(message: 'Returned CostCollection appears to be empty');
        }
        if (sizeof($costs->costList) > 1) {
            throw new IllegalValueException(message: 'Returned CostCollection contains more than one Cost');
        }

        /** @var Cost */
        return array_values($costs->costList->toArray())[0];
    }

    /**
     * Fetches translated and formatted "Starting at %1 per month..." string
     *
     * @return string
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    private function getStartingAt(): string
    {
        return str_replace(
            search: ['%1', '%2'],
            replace: [
                '<span id="rb-pp-starting-at">' . $this->getFormattedStartingAtCost() . '</span>',
                (string)$this->cost->months
            ],
            subject: Translator::translate(phraseId: 'starting-at')
        );
    }

    /**
     * Fetches formatted starting at cost with currency symbol
     *
     * @return string
     */
    public function getFormattedStartingAtCost(): string
    {
        if ($this->currencyFormat === CurrencyFormat::SYMBOL_FIRST) {
            return $this->currencySymbol . ' ' . $this->getStartingAtCost();
        }

        return $this->getStartingAtCost() . ' ' . $this->currencySymbol;
    }

    /**
     * Returns the starting at value, public visibility so that just the value can be extracted for AJAX purposes.
     *
     * @return string
     */
    public function getStartingAtCost(): string
    {
        return (string)(round(
            num: $this->cost->monthlyCost,
            precision: 2
        ));
    }

    /**
     * Fetches iframe URL
     *
     * @return string
     * @todo: Properly render URL
     */
    private function getIframeUrl(): string
    {
        /** @var PaymentMethod\LegalLink $legalLink */
        foreach ($this->paymentMethod->legalLinks as $legalLink) {
            if ($legalLink->type === LegalLinkType::PRICE_INFO) {
                return $legalLink->url . $this->amount;
            }
        }
        return '';
    }
}
