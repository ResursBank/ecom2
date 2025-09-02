<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CostList;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Cost List Javascript widget.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public const CACHE_KEY_PREFIX = 'resursbank-ecom-widget-cost-list-js';

    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $containerElDomPath,
        public readonly bool $auto = true
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . '/js.js.phtml'
        );
    }
}
