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
        $values = [
            'resurs_platform' => 'Test',
            'resurs_platform_version' => '1.0',
            'resurs_platform_plugin_version' => '1.5'
        ];

        $data = Repository::getIntegrationInfoMetadata(
            platform: $values['resurs_platform'],
            platformVersion: $values['resurs_platform_version'],
            pluginVersion: $values['resurs_platform_plugin_version']
        );

        $this->assertNotNull($data->custom, 'Received empty Entry collection.');

        foreach ($values as $key => $value) {
            if (
                $this->collectionHasKey(
                    collection: $data->custom,
                    key: $key,
                    value: $value
                )
            ) {
                $this->addToAssertionCount(count: 1);
                continue;
            }

            $this->fail(message: 'Failed on key ' . $key);
        }
    }
}
