<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Read more widget.
 */
class GetAddress extends Widget
{
    public readonly string $css;
    public readonly string $content;
    public readonly string $js;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $url = '',
        public string $govId = '',
        public CustomerType $customerType = CustomerType::NATURAL,
        public readonly bool $automatic = false
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-address.phtml');
        $this->css = $this->render(file: __DIR__ . '/get-address.css');
        $this->js = $this->render(file: __DIR__ . '/get-address.js.phtml');
    }
}
