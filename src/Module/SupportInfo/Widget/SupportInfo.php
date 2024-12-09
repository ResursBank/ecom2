<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\SupportInfo\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;
use stdClass;
use Throwable;

use function defined;

/**
 * Support info widget which displays basic information about the state of the library.
 */
class SupportInfo extends Widget
{
    private const CURL_VERSION_MIN = '7.61.0';

    /** @var string */
    public readonly string $html;

    /** @var string */
    public readonly string $css;

    /**
     * @param string $minimumPhpVersion Lowest
     * @param string $pluginVersion Version of the calling plugin/addon
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $minimumPhpVersion,
        public readonly string $maximumPhpVersion,
        public readonly string $pluginVersion = ''
    ) {
        $this->html = $this->render(file: __DIR__ . '/support-info.phtml');
        $this->css = (string) file_get_contents(
            filename: __DIR__ . '/support-info.css'
        );
    }

    /**
     * Fetches the current PHP version.
     */
    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * Fetches the current OpenSSL version.
     */
    public function getSslVersion(): string
    {
        if (defined(constant_name: 'OPENSSL_VERSION_TEXT')) {
            return OPENSSL_VERSION_TEXT;
        }

        return '';
    }

    /**
     * Fetches the current Curl version.
     */
    public function getCurlVersion(): string
    {
        $curlVersion = curl_version();

        if ($curlVersion && isset($curlVersion['version'])) {
            return $curlVersion['version'];
        }

        return '';
    }

    /**
     * Validate currently installed Curl version.
     *
     * @return array
     * @throws ConfigException
     */
    public function validateCurl(): array
    {
        $results = [];

        try {
            $results[] = $this->validateCurlVersion();
            $results[] = $this->validateCurlAuthBearerSupport();
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return $results;
    }

    /**
     * Check if there are any errors related to the installed Curl version.
     *
     * @throws ConfigException
     */
    public function hasCurlErrors(): bool
    {
        foreach ($this->validateCurl() as $result) {
            if ($result !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if there are any errors related to the installed PHP version.
     *
     * @throws ConfigException
     */
    public function hasPhpVersionErrors(): bool
    {
        return $this->validatePhpVersion() !== null;
    }

    /**
     * Validate installed PHP version against required versions.
     *
     * @throws ConfigException
     */
    public function validatePhpVersion(): ?string
    {
        try {
            // Normalize PHP versions
            $normalizedVersion = $this->normalizeVersion(
                version: $this->getPhpVersion()
            );
            $normalizedMinimum = $this->normalizeVersion(
                version: $this->minimumPhpVersion
            );
            $normalizedMaximum = $this->normalizeVersion(
                version: $this->maximumPhpVersion
            );

            if (
                version_compare(
                    version1: $normalizedVersion,
                    version2: $normalizedMinimum,
                    operator: '<'
                )
            ) {
                return Translator::translate(phraseId: 'php-version-too-old');
            }

            if (
                version_compare(
                    version1: $normalizedVersion,
                    version2: $normalizedMaximum
                ) > 0
            ) {
                return Translator::translate(phraseId: 'php-version-too-new');
            }
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return null;
    }

    /**
     * Pads shorter version numbers.
     *
     * @param string $version
     * @return string
     */
    private function normalizeVersion(string $version): string
    {
        while (count(explode(separator: '.', string: $version)) < 3) {
            $version .= '.0';
        }
        return $version;
    }

    /**
     *  Attempt to fetch the current version of Ecom from the composer.json file.
     *
     * @throws ConfigException
     */
    public function getEcomVersion(): string
    {
        return $this->getComposerData()->version;
    }

    /**
     * Check for CURLAUTH_BEARER support.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    private function validateCurlAuthBearerSupport(): ?string
    {
        if (!defined(constant_name: 'CURLAUTH_BEARER')) {
            return Translator::translate(
                phraseId: 'curlauth-bearer-support-missing'
            );
        }

        return null;
    }

    /**
     * Check if the installed Curl version is compatible with this library.
     *
     * Returns error if current Curl version is too low.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    private function validateCurlVersion(): ?string
    {
        return version_compare(
            version1: $this->getCurlVersion(),
            version2: self::CURL_VERSION_MIN
        ) < 0 ? Translator::translate(phraseId: 'curl-version-too-low') : null;
    }

    /**
     * Attempt to load composer data.
     *
     * @throws ConfigException
     */
    private function getComposerData(): stdClass
    {
        try {
            $composerJson = file_get_contents(
                filename: __DIR__ . '/../../../../composer.json'
            );

            if (!$composerJson) {
                throw new EmptyValueException(
                    message: 'Unable to load contents of composer.json'
                );
            }

            $decoded = json_decode(
                json: $composerJson,
                depth: 256,
                flags: JSON_THROW_ON_ERROR
            );

            if (!$decoded instanceof stdClass) {
                throw new IllegalTypeException(
                    message: 'Decoded JSON data not of type stdClass'
                );
            }

            return $decoded;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return new stdClass();
    }
}
