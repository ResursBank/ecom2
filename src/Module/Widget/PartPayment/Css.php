<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PartPayment;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Part payment CSS widget.
 */
class Css extends Widget
{
    public const string CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-part-payment-css';

    /** @var string */
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
