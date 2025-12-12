<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CallbackTest;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * Callback test button widget Javascript.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-callback-test-button-js';

    public readonly string $content;

    /**
     * URL for callback test.
     */
    public string $url = '';

    /**
     * URL to fetch the received at timestamp.
     */
    public string $receivedAtUrl = '';

    /**
     * @throws ConfigException
     * @throws FilesystemException
     * @throws UserSettingsException
     * @throws IllegalUrlException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly bool $automatic = true
    ) {
        $this->url = Repository::getUrl(url: Url::CALLBACK_TEST_TRIGGER_URL);
        $this->receivedAtUrl = Repository::getUrl(url: Url::CALLBACK_TEST_RECEIVED_AT_URL);

        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }

    /**
     * Check if widget should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->url !== '';
    }
}
