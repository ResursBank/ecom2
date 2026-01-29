<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\TestPurchase;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Test purchase JS widget.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public readonly string $content;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $controllerUrl,
        public readonly CountryCode $countryCode
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }
}
