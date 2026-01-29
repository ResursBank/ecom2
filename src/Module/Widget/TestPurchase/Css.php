<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\TestPurchase;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Test purchase CSS widget.
 */
class Css extends Widget
{
    public string $content;

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
