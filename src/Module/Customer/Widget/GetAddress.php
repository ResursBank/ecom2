<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Store\Enum\Country;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Throwable;

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
     * @throws ConfigException
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function __construct(
        public readonly string $url = '',
        public string $govId = '',
        public CustomerType $customerType = CustomerType::NATURAL,
        public readonly bool $automatic = false
    ) {
        if ($this->shouldRender()) {
            $this->content = $this->render(
                file: __DIR__ . '/get-address.phtml'
            );
            $this->css = $this->render(file: __DIR__ . '/get-address.css');
            $this->js = $this->render(file: __DIR__ . '/get-address.js.phtml');
        } else {
            $this->content = '';
            $this->css = '';
            $this->js = '';
        }
    }

    /**
     * Check if widget should be rendered.
     *
     * The widget should only render for stores in Sweden.
     *
     * @throws ConfigException
     */
    private function shouldRender(): bool
    {
        try {
            $stores = StoreRepository::getStores(size: 1);

            if (Config::getStoreId() !== null) {
                $store = $stores->filterById(id: Config::getStoreId());

                if ($store !== null && $store->countryCode === Country::SE) {
                    return true;
                }
            }
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            return false;
        }

        return false;
    }
}
