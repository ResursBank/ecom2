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
 * Payment methods table widget.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-payment-method-html';

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
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    public function getCustomerTypes(PaymentMethod $method): string
    {
        $valid = [];

        if ($method->enabledForNaturalCustomer) {
            $valid[] = 'NATURAL';
        }

        if ($method->enabledForLegalCustomer) {
            $valid[] = 'LEGAL';
        }

        return implode(separator: '/', array: $valid);
    }
}
