<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CacheManagement;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\UserSettings\Repository;

/**
 * Render Cache Management Button Widget JavaScript.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    use RenderTrait;

    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-cache-management-js';

    public readonly string $content;

    /**
     * URL for cache clear endpoint.
     */
    public string $url = '';

    /**
     * @throws ConfigException
     * @throws FilesystemException
     * @throws UserSettingsException
     * @throws IllegalUrlException
     */
    public function __construct()
    {
        $this->url = Repository::getUrl(url: Url::CACHE_CLEAR_URL);

        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }
}
