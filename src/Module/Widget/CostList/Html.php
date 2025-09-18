<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CostList;

use JsonException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Widget displaying price signage cost list.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-cost-list-html';

    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct(
        public readonly PriceSignage $priceSignage,
        public readonly PaymentMethod $method
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . '/html.phtml'
        );
    }

    /**
     * @inheritDoc
     */
    public function shouldRender(): bool
    {
        return $this->priceSignage->costList->count() > 0 &&
            $this->method->type !== Type::RESURS_INVOICE;
    }

    /**
     * Fetches translated and formatted setup fee string.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     */
    public function getSetupFee(Cost $cost): string
    {
        return Translator::translate(phraseId: 'setup-fee') . ' ' .
            $this->getFormattedCost(cost: $cost->setupFee);
    }

    /**
     * Fetches translated and formatted administration fee string.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     */
    public function getAdministrationFee(Cost $cost): string
    {
        return Translator::translate(phraseId: 'administration-fee') . ' ' .
            $this->getFormattedCost(cost: $cost->administrationFee);
    }

    /**
     * Fetches translated and formatted duration text.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     */
    public function getDuration(Cost $cost): string
    {
        return str_replace(
            search: '%1',
            replace: (string) $cost->durationMonths,
            subject: Translator::translate(phraseId: 'part-payment-duration')
        );
    }

    /**
     * Fetches formatted starting at cost with currency symbol.
     *
     * @throws ConfigException
     */
    private function getFormattedCost(float $cost): string
    {
        return Price::format(value: $cost);
    }
}
