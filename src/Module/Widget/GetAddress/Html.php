<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\GetAddress;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\Country;
use Resursbank\Ecom\Lib\Model\CustomerType;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * Get address widget Javascript.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-get-address-html';

    public readonly string $content;

    /**
     * @param string $inputClassList CSS classes for input
     * @param string $btnClassList CSS classes for button
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct(
        public string $govId = '',
        public CustomerType $customerType = CustomerType::NATURAL,
        public readonly string $inputClassList = '',
        public readonly string $btnClassList = ''
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Check if widget should be rendered.
     *
     * The widget should only render if:
     *
     * 1. Get Address is enabled in settings.
     * 2. Store country is Sweden.
     *
     * @throws ConfigException
     * @todo Update tests, move this to a trait since it's duplicated in JS & CSS classes.
     */
    public function shouldRender(): bool
    {
        try {
            $settings = Repository::getSettings();
            $store = StoreRepository::getConfiguredStore();

            if (
                $settings->enableGetAddress &&
                $store !== null &&
                $store->countryCode === Country::SE
            ) {
                return true;
            }
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            return false;
        }

        return false;
    }
}
