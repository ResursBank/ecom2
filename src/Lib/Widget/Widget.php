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
    public const CACHE_KEY_PREFIX = '';

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
     * @throws ConfigException
     */
    public function renderStatic(string $file): string
    {
        if ($this->canCacheData()) {
            return $this->renderStaticWithCache(file: $file);
        }

        return $this->renderStaticWithoutCache(file: $file);
    }

    /**
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public function renderStaticWithoutCache(string $file): string
    {
        if (!$this->shouldRender()) {
            return '';
        }

        if (!file_exists(filename: $file)) {
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

        $content = file_get_contents(filename: $file);

        if ($content === false) {
            $this->handleFileReadFailure(filename: $file);
            return '';
        }

        return $content;
    }

    /**
     * @param string $file File to load
     * @return string Loaded file or empty string (if loading failed))
     * @throws ConfigException
     */
    public function renderStaticWithCache(string $file): string
    {
        $result = $this->getCachedContent();

        if (!is_string(value: $result)) {
            $result = $this->renderStaticWithoutCache(file: $file);
            $this->setCachedContent(data: $result);
        }

        return $result;
    }

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function render(string $file): string
    {
        if ($this->canCacheData()) {
            return $this->renderWithCache(file: $file);
        }

        return $this->renderWithoutCache(file: $file);
    }

    /**
     * @throws FilesystemException
     */
    public function renderWithoutCache(
        string $file
    ): string {
        if (!$this->shouldRender()) {
            return '';
        }

        try {
            if (!file_exists(filename: $file)) {
                throw new FilesystemException(
                    message: self::class . '::' . __METHOD__ .
                    ': File: ' . $file . ' does not exist.'
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

            ob_clean();
            return '';
        }
    }

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function renderWithCache(string $file): string
    {
        $result = $this->getCachedContent();

        if (!is_string(value: $result)) {
            $result = $this->renderWithoutCache(file: $file);
            $this->setCachedContent(data: $result);
        }

        return $result;
    }

    /**
     * Check if widget data can be cached.
     *
     * This method exists as it does so that if necessary individual widgets
     * can override it if for example they should never cache their data.
     *
     * @throws ConfigException
     */
    public function canCacheData(): bool
    {
        return Config::getCacheWidgets() && $this::CACHE_KEY_PREFIX !== '';
    }

    /**
     * Retrieve cache key for widget.
     *
     * This method relies on the CACHE_KEY_PREFIX constant which should be set
     * in each child widget.
     */
    public function getCacheKey(): string
    {
        return $this::CACHE_KEY_PREFIX . '-' . Config::getStoreId() . '_' .
            sha1(string: serialize(value: $this));
    }

    /**
     * Check if widget should be rendered.
     *
     * This method exists to be overridden by child class implementations.
     */
    public function shouldRender(): bool
    {
        return true;
    }

    /**
     * @throws ConfigException
     */
    protected function getCachedContent(): ?string
    {
        return Config::getCache()->read(
            key: $this->getCacheKey() . '-content'
        );
    }

    /**
     * @throws ConfigException
     */
    protected function setCachedContent(string $data): void
    {
        Config::getCache()->write(
            key: $this->getCacheKey() . '-content',
            data: $data,
            ttl: 3600
        );
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
