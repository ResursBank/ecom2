<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PartPayment;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Widget\PartPayment\Traits\Common;
use Throwable;

/**
 * Renders Part payment widget JS
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Js extends Widget
{
    use Common;

    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-part-payment-js';

    /** @var string */
    public readonly string $content;

    /** @var Cost */
    public readonly Cost $cost;

    /** @var bool */
    public readonly bool $shouldDisplayCostExample;

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Throwable
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly int $months,
        public readonly float $amount,
        #[StringIsUrl] public readonly string $fetchStartingCostUrl,
        public readonly float $threshold = 0.0,
        public readonly bool $showCostExample = true
    ) {
        $this->cost = $this->getCost(
            paymentMethod: $this->paymentMethod,
            amount: $this->amount,
            months: $this->months
        );

        $this->shouldDisplayCostExample = $this->shouldDisplayCostExample(
            threshold: $this->threshold,
            cost: $this->cost,
            paymentMethod: $this->paymentMethod,
            showCostExample: $this->showCostExample
        );

        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }
}
