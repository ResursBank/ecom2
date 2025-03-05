<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Renders the Javascript for the ReadMore widget.
 */
class ReadMoreJs extends Widget
{
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $containerElDomPath,
        public readonly bool $autoInitJs = true
    ) {
        $this->content = $this->render(file: __DIR__ . '/read-more.js.phtml');
    }
}
