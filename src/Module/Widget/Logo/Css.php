<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\Logo;

use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Render logotype CSS.
 */
class Css extends Widget
{
    public readonly string $content;

    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'logo.css'
        );
    }
}
