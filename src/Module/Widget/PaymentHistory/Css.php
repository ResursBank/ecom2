<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentHistory;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Payment history log CSS widget.
 */
class Css extends Widget
{
    public const CACHE_KEY_PREFIX = 'resursbank-ecom-widget-payment-history-css';

    public readonly string $content;

    /**
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'css.css'
        );
    }
}
