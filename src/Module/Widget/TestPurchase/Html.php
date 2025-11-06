<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\TestPurchase;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Test purchase widget.
 */
class Html extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function __construct()
    {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }
}
