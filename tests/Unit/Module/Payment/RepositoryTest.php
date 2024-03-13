<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Payment;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Payment\Metadata\Entry;
use Resursbank\Ecom\Lib\Model\Payment\Metadata\EntryCollection;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Unit tests for Payment\Repository.
 */
class RepositoryTest extends TestCase
{
    /**
     * Check if Entry collection has specified key/value Entry.
     */
    private function collectionHasKey(
        EntryCollection $collection,
        string $key,
        string $value
    ): bool {
        /** @var Entry $item */
        foreach ($collection as $item) {
            if ($item->key === $key && $item->value === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify that data is properly stored in platform info metadata.
     *
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetIntegrationInfoMetadata(): void
    {
        $platform = 'Test';
        $platformVersion = '1.0';
        $pluginVersion = '1.5';
        $data = Repository::getIntegrationInfoMetadata(
            platform: $platform,
            platformVersion: $platformVersion,
            pluginVersion: $pluginVersion
        );

        if ($data->custom === null) {
            $this->fail(message: 'Received empty Entry collection.');
        }

        if (
            $this->collectionHasKey(
                collection: $data->custom,
                key: 'resurs_platform',
                value: $platform
            )
        ) {
            $this->addToAssertionCount(count: 1);
        } else {
            $this->fail(message: "'resurs_platform' value not found!");
        }

        if (
            $this->collectionHasKey(
                collection: $data->custom,
                key: 'resurs_platform_version',
                value: $platformVersion
            )
        ) {
            $this->addToAssertionCount(count: 1);
        } else {
            $this->fail(message: "'resurs_platform_version' value not found!");
        }

        if (
            $this->collectionHasKey(
                collection: $data->custom,
                key: 'resurs_platform_plugin_version',
                value: $pluginVersion
            )
        ) {
            $this->addToAssertionCount(count: 1);
        } else {
            $this->fail(
                message: "'resurs_platform_plugin_version' value not found!"
            );
        }
    }
}
