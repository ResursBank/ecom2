<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Widget;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Display warning message widget.
 */
class Warning extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct() {
        $this->content = \Resursbank\Ecom\Config::getLocation() === Location::SE ?
            $this->render(file: __DIR__ . '/warning.phtml') : '';
    }
}
