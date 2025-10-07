<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentInformation;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Renders Payment Information CSS for use in admin panel order view
 */
class Css extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-payment-information-css';

    public readonly string $content;

    /**
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'css.css'
        );
    }
}
