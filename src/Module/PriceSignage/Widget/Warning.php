<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Location;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Display warning message widget.
 */
class Warning extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param PriceSignage $priceSignage
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PriceSignage $priceSignage
    ) {
        $this->content = $this->isDisplayed() ? $this->render(file: __DIR__ . '/warning.phtml') : '';
    }

    /**
     * Only display the warning if the cost list is not empty and the location is SE.
     */
    public function isDisplayed(): bool
    {
        return $this->priceSignage->costList->count() > 0 &&
            Config::getLocation() === Location::SE;
    }
}
