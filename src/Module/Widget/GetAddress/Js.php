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
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Store\Enum\Country;
use Resursbank\Ecom\Module\Store\Repository as StoreRepository;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * Get address widget Javascript.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-get-address-js';

    public readonly string $content;

    /**
     * @throws FilesystemException
     * @throws IllegalValueException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $url,
        public readonly bool $automatic = false,
        StringValidation $stringValidation = new StringValidation()
    ) {
        $stringValidation->isUrl(value: $url);

        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
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

            if ($settings->enableGetAddress &&
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
