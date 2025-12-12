<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CallbackTest;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Lib\UserSettings\Url;
use Resursbank\Ecom\Module\UserSettings\Repository;

/**
 * Trait to provide shouldRender logic for CallbackTest widgets.
 */
trait RenderTrait
{
    /**
     * Check if widget should be rendered.
     *
     * @throws ConfigException
     * @throws UserSettingsException
     * @throws IllegalUrlException
     */
    public function shouldRender(): bool
    {
        $triggerUrl = Repository::getUrl(url: Url::CALLBACK_TEST_TRIGGER_URL);
        $receivedAtUrl = Repository::getUrl(url: Url::CALLBACK_TEST_RECEIVED_AT_URL);
        return $triggerUrl !== '' && $receivedAtUrl !== '';
    }
}

