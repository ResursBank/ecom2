<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

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

    /** @var string  */
    public readonly string $css;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PaymentMethod $method,
        public readonly PriceSignage $priceSignage
    ) {
        $this->content = $this->priceSignage->costList->count() > 0 ?
            $this->render(file: __DIR__ . '/cost-list.phtml') : '';
        $this->css = $this->render(file: __DIR__ . '/cost-list.css');
    }
}
