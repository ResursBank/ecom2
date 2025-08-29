<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\Loader;

use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Renders Loader HTML for use in admin panel order view
 */
class Html extends Widget
{
    public readonly string $content;

    public function __construct(
        public readonly bool $display = false
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }
}
