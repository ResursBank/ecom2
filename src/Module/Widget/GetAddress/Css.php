<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\GetAddress;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Model\Country;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * Get address widget CSS.
 */
class Css extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-get-address-css';

    public readonly string $content;

    /**
     * @throws ConfigException
     */
    public function __construct()
    {
        $this->content = $this->renderStatic(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'css.css'
        );
    }

    /**
     * Check if widget should be rendered.
     *
     * The widget should only render for stores in Sweden.
     *
     * @throws ConfigException
     * @todo Move to trait, see Html class duplicate for more info.
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
