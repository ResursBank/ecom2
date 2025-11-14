<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentMethod;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * This widget will render a JavaScript component you can interact
 * with to get information about payment methods, like if a method
 * is available based on context (cart total, country etc.).
 */
class Js extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-payment-method-js';

    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct(
        public readonly PaymentMethodCollection $paymentMethods
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }

    /**
     * Returns only the data needed for isAvailable logic, keyed by method id.
     */
    public function getData(): array
    {
        $data = [];

        /** @var PaymentMethod $method */
        foreach ($this->paymentMethods as $method) {
            $data[$method->id] = [
                'minPurchaseLimit' => $method->minPurchaseLimit,
                'maxPurchaseLimit' => $method->maxPurchaseLimit,
                'enabledForLegalCustomer' => $method->enabledForLegalCustomer,
                'enabledForNaturalCustomer' => $method->enabledForNaturalCustomer,
                'isInternal' => $method->isInternal()
            ];
        }
        return $data;
    }
}
