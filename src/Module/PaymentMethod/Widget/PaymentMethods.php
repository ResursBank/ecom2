<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;

/**
 * Payment methods table widget.
 * @psalm
 */
class PaymentMethods extends Widget
{
    /**
     * @var string
     */
    public string $url = '';

    /**
     * @var string
     */
    public string $content = '';

    /**
     * @var string
     */
    public string $nameLabel;

    /**
     * @var string
     */
    public string $minTotalLabel;

    /**
     * @var string
     */
    public string $maxTotalLabel;

    /**
     * @var string
     */
    public string $sortOrderLabel;

    /**
     * @param PaymentMethodCollection $paymentMethods
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     */
    public function __construct(
        public readonly PaymentMethodCollection $paymentMethods,
    ) {
        $this->nameLabel = Translator::translate('name');
        $this->minTotalLabel = Translator::translate('min-total');
        $this->maxTotalLabel = Translator::translate('max-total');
        $this->sortOrderLabel = Translator::translate('sort-order');
        $this->content = $this->render(file: __DIR__ . '/payment-methods.phtml');
    }
}
