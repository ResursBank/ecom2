<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CacheManagement;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Render Cache Management Button Widget HTML.
 */
class Html extends Widget
{
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Check if widget should be rendered.
     */
    public function shouldRender(): bool
    {
        return true;
    }
}