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
     * Render a static template (e.g. Javascript or CSS)
     *
     * @param string $file File to load
     * @return string Loaded file or empty string (if loading failed)
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public function renderStatic(string $file): string
    {
        if (!file_exists($file)) {
            try {
                Config::getLogger()->error(
                    message: self::class . '::' . __METHOD__ .
                    ': File ' . $file . ' does not exist.'
                );
            } catch (ConfigException) {
                // Do nothing just to prevent ConfigExceptions breaking
                // the rendering of the widget.
            }

            return '';
        }

        $content = file_get_contents($file);

        if ($content === false) {
            $this->handleFileReadFailure(filename: $file);
            return '';
        }

        return $content;
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

    /**
     * Log file read error.
     *
     * @param string $filename Name of file that couldn't be read.
     */
    private function handleFileReadFailure(string $filename): void
    {
        try {
            Config::getLogger()->error(
                message: 'File ' . $filename . ' could not be read.'
            );
        } catch (ConfigException) {
            // Do nothing just to prevent ConfigExceptions breaking
            // the rendering of the widget.
        }
    }
}
