<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Lib\UserSettings\ReaderInterface;
use Resursbank\Ecom\Lib\UserSettings\Url;

/**
 * Dummy settings reader for use with integration and unit tests.
 *
 * This class uses a mix of hard-coded values and values loaded from the
 * PhpUnit configuration file.
 */
class DummySettingsReader implements ReaderInterface
{
    public function __construct(
        private readonly ?LogLevel $logLevel = null
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function read(Field $field): ?string
    {
        switch ($field->name) {
            case 'API_TIMEOUT':
                return '60';

            case 'CACHE_ENABLED':
                return 'false';

            case 'ENVIRONMENT':
                return 'test';

            case 'CLIENT_ID_TEST':
                return $_ENV['JWT_AUTH_CLIENT_ID'];

            case 'CLIENT_SECRET_TEST':
                return $_ENV['JWT_AUTH_CLIENT_SECRET'];

            case 'LOG_ENABLED':
                return 'true';

            case 'LOG_LEVEL':
                if ($this->logLevel === null) {
                    return (string)LogLevel::INFO->value;
                }

                return (string)$this->logLevel->value;

            case 'LOG_DIR':
                return '/tmp';

            case 'STORE_ID':
                return $_ENV['STORE_ID'];

            case 'PART_PAYMENT_ENABLED':
                return 'true';

            case 'PART_PAYMENT_METHOD_ID':
                return $_ENV['ANNUITY_PAYMENT_METHOD_ID'];

            case 'PART_PAYMENT_THRESHOLD':
                return '150';

            case 'PART_PAYMENT_PERIOD':
                return '3';
        }

        file_put_contents(
            filename: '/tmp/ecom-settings-fields.txt',
            data: $field->name . " - " . $field->value . PHP_EOL,
            flags: FILE_APPEND
        );

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(Field $field, mixed $value): void
    {
        return;
    }

    public function getUrl(Url $url): ?string
    {
        // TODO: Implement getUrl() method.
        switch ($url->name) {
            case 'PART_PAYMENT_AJAX_URL':
                return 'https://example.com';

            case 'FETCH_STORES':
                return 'https://example.com';

            default:
                file_put_contents(
                    filename: '/tmp/ecom-settings-urls.txt',
                    data: $url->name . " - " . $url->value . PHP_EOL,
                    flags: FILE_APPEND
                );
                break;
        }

        return null;
    }
}
