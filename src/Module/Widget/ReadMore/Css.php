<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\ReadMore;

use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Read More widget CSS.
 */
class Css extends Widget
{
    /** @var string */
    public readonly string $content;

    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'css.css'
        );
    }
}
