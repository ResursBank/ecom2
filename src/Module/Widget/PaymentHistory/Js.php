<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentHistory;

use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Payment history log javascript widget.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public readonly string $content;

    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js'
        );
    }
}
