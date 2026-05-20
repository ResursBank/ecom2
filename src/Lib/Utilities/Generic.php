<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function dirname;
use function is_object;
use function is_string;

/**
 * Generic Utils Class for things that is good to have.
 *
// phpcs:ignore
 * @version 1.0.0
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
// phpcs:ignore
class Generic
{
    private object $composerData;

    private string $composerLocation;

    /**
     * @throws Exception
     */
    public function getComposerVendor(string $composerLocation): string
    {
        return $this->getNameEntry(
            part: 'vendor',
            composerLocation: $composerLocation
        );
    }

    /**
     * Extract a tag from composer.json.
     *
     * @throws Exception
     */
    public function getComposerTag(string $location, string $tag): string
    {
        $return = '';

        // @todo Object should be defined as stdClass or more specific object.

        if (empty($this->composerData)) {
            $this->getComposerConfig(location: $location);
        }

        if (
            isset($this->composerData->{$tag}) &&
            is_string(value: $this->composerData->{$tag})
        ) {
            $return = (string)$this->composerData->{$tag};
        }

        return $return;
    }

    /**
     * @param string $location Location of composer.json.
     * @param int $maxDepth How deep the search for a composer.json will be. Usually you should not need more than 3.
     * @throws Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getComposerConfig(string $location, int $maxDepth = 3): string
    {
        // Pre-check if file exists.
        $this->throwIfFileNonexistent(location: $location);

        $startAt = dirname(path: $location);

        if ($this->hasComposerFile(location: $startAt)) {
            $this->getComposerConfigData(location: $startAt);
            return $startAt;
        }

        $composerLocation = $this->findComposerLocation(
            maxDepth: $maxDepth,
            startAt: $startAt
        );

        if ($composerLocation === null) {
            throw new IllegalValueException(message: 'No composer.json found');
        }

        $this->getComposerConfigData(location: $composerLocation);

        return $this->composerLocation;
    }

    /**
     * Using both class and composer.json to discover version (in case that composer.json are removed in a "final").
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function getVersionByAny(
        string $composerLocation = '',
        int $composerDepth = 3,
        string $className = ''
    ): string {
        $return = '';

        $byComposer = $this->getVersionByComposer(
            location: $composerLocation,
            maxDepth: $composerDepth
        );
        $byClass = $this->getVersionByClassDoc(className: $className);

        // Composer always have higher priority.
        if (!empty($byComposer)) {
            $return = $byComposer;
        } elseif (!empty($byClass)) {
            $return = $byClass;
        }

        return $return;
    }

    /**
     * @param int $maxDepth Default is 3.
     * @throws Exception
     */
    public function getVersionByComposer(string $location, int $maxDepth = 3): string
    {
        if (
            empty($this->getComposerConfig(
                location: $location,
                maxDepth: $maxDepth
            ))
        ) {
            return '';
        }

        return $this->getComposerTag(
            location: $this->composerLocation,
            tag: 'version'
        );
    }

    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getVersionByClassDoc(string $className = ''): string
    {
        return $this->getDocBlockItem(item: '@version', className: $className);
    }

    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getDocBlockItem(string $item, string $functionName = '', string $className = ''): string
    {
        return Generic\Docblock::getExtractedDocBlockItem(
            item: $item,
            doc: Generic\Docblock::getExtractedDocBlock(
                functionName: $functionName,
                className: $className
            )
        );
    }

    /**
     * @param string $part Defines which part of the vendor row you want (name or the vendor itself)
     * @param string $composerLocation Where composer.json are stored.
     * @throws Exception
     * @noinspection PhpSameParameterValueInspection
     */
    private function getNameEntry(string $part, string $composerLocation): string
    {
        $return = '';
        $composerNameEntry = explode(
            separator: '/',
            string: $this->getComposerTag(
                location: $composerLocation,
                tag: 'name'
            ),
            limit: 2
        );

        switch ($part) {
            case 'name':
                if (isset($composerNameEntry[1])) {
                    $return = $composerNameEntry[1];
                }

                break;

            case 'vendor':
                if (isset($composerNameEntry[0])) {
                    $return = $composerNameEntry[0];
                }

                break;

            default:
                break;
        }

        return $return;
    }

    /**
     * Check for composer file.
     */
    private function hasComposerFile(string $location): bool
    {
        return file_exists(filename: sprintf('%s/composer.json', $location));
    }

    /**
     * @throws JsonException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    private function getComposerConfigData(string $location): void
    {
        $this->composerLocation = $location;

        $getFrom = sprintf('%s/composer.json', $location);

        if (!file_exists(filename: $getFrom)) {
            return;
        }

        $data = null;
        $json = file_get_contents(filename: $getFrom);

        if ($json !== false && $json !== '') {
            $data = json_decode(
                json: $json,
                associative: false,
                depth: 768,
                flags: JSON_THROW_ON_ERROR
            );
        }

        if (!is_object(value: $data)) {
            return;
        }

        $this->composerData = $data;
    }

    /**
     * Throw FilesystemException if specified file doesn't exist.
     *
     * @throws FilesystemException
     */
    private function throwIfFileNonexistent(string $location): void
    {
        if (!file_exists(filename: $location)) {
            throw new FilesystemException(message: 'Invalid path', code: 1013);
        }
    }

    /**
     * Find composer.json location.
     */
    private function findComposerLocation(int $maxDepth, string $startAt): ?string
    {
        $composerLocation = null;

        while ($maxDepth--) {
            $startAt .= '/..';

            if ($this->hasComposerFile(location: $startAt)) {
                $composerLocation = $startAt;
                break;
            }
        }

        return $composerLocation;
    }
}
