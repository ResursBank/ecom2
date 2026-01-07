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
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Throwable;

/**
 * Callback test widget HTML.
 */
class Html extends Widget
{
    use RenderTrait;

    public readonly string $content;

    /**
     * Timestamp when last test callback was received.
     */
    public readonly ?int $testReceivedAt;

    /**
     * @throws FilesystemException
     * @throws ConfigException
     */
    public function __construct()
    {
        // Get last callback received timestamp from UserSettings
        try {
            $settings = Repository::getSettings();
            $this->testReceivedAt = $settings->testReceivedAt;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            $this->testReceivedAt = null;
        }

        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }
}
