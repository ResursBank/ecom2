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
    /**
     * Rendered get-address.css file.
     */
    public readonly string $css;

    /**
     * Rendered get-address.phtml file.
     */
    public readonly string $content;

    /**
     * Rendered get-address.js.phtml file.
     */
    public readonly string $js;

    /**
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $url = '',
        public string $govId = '',
        public CustomerType $customerType = CustomerType::NATURAL,
        public readonly bool $automatic = false,
        public readonly array $selectableCustomerTypes = [
            CustomerType::NATURAL,
            CustomerType::LEGAL
        ]
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-address.phtml');
        $this->css = $this->render(file: __DIR__ . '/get-address.css');
        $this->js = $this->render(file: __DIR__ . '/get-address.js.phtml');
    }

    /**
     * Checks if the provided customer type matches the current customer type.
     */
    public function isCustomerTypeChecked(CustomerType $customerType): bool
    {
        return $this->customerType === $customerType;
    }

    /**
     * Determines if the radio buttons can be hidden based on customer type and selectable types.
     */
    public function canHideRadioButtons(CustomerType $customerType): bool
    {
        return count($this->selectableCustomerTypes) === 1 && in_array(
            needle: $customerType,
            haystack: $this->selectableCustomerTypes,
            strict: true
        );
    }
}
