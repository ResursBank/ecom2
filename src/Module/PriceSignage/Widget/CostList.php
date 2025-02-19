<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Widget displaying price signage cost list.
 */
class CostList extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PriceSignage $priceSignage,
        public readonly PaymentMethod $method
    ) {
        $this->content = $this->priceSignage->costList->count() > 0 ?
            $this->render(file: __DIR__ . '/cost-list.phtml') : '';
    }

    /**
     * Not a property because we may want to render it separately. For example, if rendering the widget HTML in one
     * place, but needing the CSS in a different place. Having this defined as a property on this widget would cause
     * that to render the widget twice needlessly just to access the CSS.
     */
    public static function getCss(): string
    {
        return file_get_contents(__DIR__ . '/cost-list.css');
    }

    /**
     * Not a property because we may want to render it separately. For example, if rendering the widget HTML in one
     * place, but needing the JS in a different place. Having this defined as a property on this widget would cause
     * that to render the widget twice needlessly just to access the JS.
     */
    public static function getJs(): string
    {
        return file_get_contents(__DIR__ . '/cost-list.js.phtml');
    }
}
