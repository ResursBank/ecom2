<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
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
     * @throws FilesystemException
     */
    public function __construct(
        public readonly PriceSignage $priceSignage
    ) {
        $this->content = $this->render(file: __DIR__ . '/warning.phtml');
    }
}
