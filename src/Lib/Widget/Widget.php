<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Throwable;

/**
 * Basic widget functionality.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Widget
{
    /**
     * Get list of unique tag names in rendered content.
     *
     * This is useful to platforms requiring us to escape content we echo.
     */
    public static function getTagNames(string $content): array
    {
        $tagNames = [];

        preg_match_all(
            pattern: '/<([a-zA-Z0-9\-]+)\b[^>]*>/',
            subject: $content,
            matches: $tagNames
        );

        return empty($tagNames[1]) ? [] : array_unique(array: $tagNames[1]);
    }

    /**
     * @throws FilesystemException
     */
    public function render(
        string $file
    ): string {
        try {
            if (!file_exists(filename: $file)) {
                throw new FilesystemException(
                    message: "Template file not found: $file"
                );
            }

            ob_start();
            require $file;
            return (string)ob_get_clean();
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error(message: $error);
            } catch (ConfigException) {
                // Do nothing just to prevent ConfigExceptions breaking
                // the rendering of the widget.
            }

            return '';
        }
    }
}
