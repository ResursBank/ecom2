<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Widget\Widget;
use Throwable;

/**
 * Widget displaying price signage cost list.
 */
class CostList extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PriceSignage $priceSignage,
        public readonly PaymentMethod $method,
        public readonly int $decimals = 2
    ) {
        $this->content = ($this->priceSignage->costList->count() > 0 &&
            $this->method->type !== Type::RESURS_INVOICE) ?
            $this->render(file: __DIR__ . '/cost-list.phtml') : '';
    }

    /**
     * Not a property because we may want to render it separately. For example, if rendering the widget HTML in one
     * place, but needing the CSS in a different place. Having this defined as a property on this widget would cause
     * that to render the widget twice needlessly just to access the CSS.
     */
    public static function getCss(): string
    {
        return file_get_contents(__DIR__ . '/cost-list.css');
    }

    /**
     * Fetches translated and formatted setup fee string.
     *
     * @throws ConfigException
     */
    public function getSetupFee(Cost $cost): string
    {
        try {
            return Translator::translate(phraseId: 'setup-fee') . ' ' .
                $this->getFormattedCost(cost: $cost->setupFee);
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches translated and formatted administration fee string.
     *
     * @throws ConfigException
     */
    public function getAdministrationFee(Cost $cost): string
    {
        try {
            return Translator::translate(phraseId: 'administration-fee') . ' ' .
                $this->getFormattedCost(cost: $cost->administrationFee);
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches translated and formatted duration text.
     *
     * @throws ConfigException
     */
    public function getDuration(Cost $cost): string
    {
        try {
            return str_replace(
                '%1',
                (string) $cost->durationMonths,
                Translator::translate(phraseId: 'part-payment-duration')
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Fetches formatted starting at cost with currency symbol.
     *
     * @throws ConfigException
     */
    private function getFormattedCost(float $cost): string
    {
        return Price::format(value: $cost, decimals: $this->decimals);
    }
}
