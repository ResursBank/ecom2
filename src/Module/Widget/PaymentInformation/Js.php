<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentInformation;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * JavaScript to reload the payment information widget on demand.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param float $amount Current order amount when entering view.
     * @param string $reloadUrl URL to fetch new widget HTML from.
     * @param string $amountElement DOM path to amount element.
     * @param array $observableElements List of DOM paths to trigger reload on.
     * @param bool $automatic Whether to initiate JS automatically.
     * @throws FilesystemException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $reloadUrl,
        public readonly string $widgetElement,
        public readonly string $amountElement,
        public readonly array $observableElements,
        public readonly bool $automatic = true
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . '/templates/js.js.phtml'
        );
    }
}
