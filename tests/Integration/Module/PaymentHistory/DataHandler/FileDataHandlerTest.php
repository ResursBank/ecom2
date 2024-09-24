<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory\DataHandler;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\FileDataHandler;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\EcomTest\Utilities\PaymentHistory;

/**
 * Integration test to confirm functionality of bundled payment history
 * storage class.
 */
class FileDataHandlerTest extends PaymentHistory
{
    private FileDataHandler $handler;

    private string $testFilePath = '/tmp/resursbank/ecom/payment-history.json';

    /**
     * Create storage file.
     */
    protected function setUp(): void
    {
        $directory = dirname(path: $this->testFilePath);

        if (!is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0777, recursive: true);
        }

        $this->handler = new FileDataHandler(file: $this->testFilePath);

        parent::setUp();
    }

    /**
     * Delete storage file.
     */
    protected function tearDown(): void
    {
        if (file_exists(filename: $this->testFilePath)) {
            unlink(filename: $this->testFilePath);
        }

        parent::tearDown();
    }

    /**
     * Assert write functionality works by reading file contents back manually.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function testWrite(): void
    {
        $entry = $this->getEntry();
        $this->handler->write(entry: $entry);

        $json = file_get_contents(filename: $this->testFilePath);

        $this->assertIsString(actual: $json);
        $this->assertNotEmpty(actual: $json);

        $data = json_decode(json: $json);

        $this->assertIsArray(actual: $data);

        $content = DataConverter::arrayToCollection(
            data: $data,
            type: Entry::class
        );

        $this->assertInstanceOf(
            expected: EntryCollection::class,
            actual: $content
        );
        $this->assertCount(expectedCount: 1, haystack: $content);
        $this->assertEquals(
            expected: $entry,
            actual: $content->current()
        );
    }

    /**
     * Assert that we can read a storage file and filter content on paymentId.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetList(): void
    {
        /* Write single entry to file, get it back from the file, make sure data
           is intact. */
        $entry1 = $this->getEntry();
        $this->handler->write(entry: $entry1);

        $collection1 = $this->handler->getList(paymentId: $entry1->paymentId);

        $this->assertNotNull(actual: $collection1);
        $this->assertCount(expectedCount: 1, haystack: $collection1);
        $this->assertEquals(
            expected: new EntryCollection(data: [$entry1]),
            actual: $collection1
        );

        /* Write multiple entries to the file, pick up only the new entries
           ignoring the one from our previous tests above. This ensures
           filtering by paymentId works as expected. */
        $paymentId = Strings::getUuid();

        $entry2 = $this->getEntry(paymentId: $paymentId, result: Result::INFO);
        $this->handler->write(entry: $entry2);

        $entry3 = $this->getEntry(paymentId: $paymentId, result: Result::INFO);
        $this->handler->write(entry: $entry3);

        $collection2 = $this->handler->getList(paymentId: $paymentId);

        $this->assertNotNull(actual: $collection2);
        $this->assertCount(expectedCount: 2, haystack: $collection2);
        $this->assertEquals(
            expected: new EntryCollection(data: [$entry2, $entry3]),
            actual: $collection2
        );
    }

    /**
     * Assert that filtering collection on payment id / event works.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFilterListOnEvent(): void
    {
        /** @var Entry $entry */
        foreach ($this->getEntries() as $entry) {
            $this->handler->write(entry: $entry);
        }

        $paymentId1 = Strings::getUuid();
        $entries = [
            $this->getEntry(paymentId: $paymentId1, event: Event::REFUNDED),
            $this->getEntry(paymentId: $paymentId1, event: Event::REFUNDED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CAPTURED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CAPTURED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::REFUNDED),
            $this->getEntry(paymentId: $paymentId1, event: Event::REFUNDED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CAPTURED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId1, event: Event::CANCELED),
        ];

        foreach ($entries as $entry) {
            $this->handler->write(entry: $entry);
        }

        $paymentId2 = Strings::getUuid();
        $entries2 = [
            $this->getEntry(paymentId: $paymentId2, event: Event::REFUNDED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::REDIRECTED_TO_GATEWAY
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::CAPTURED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::PARTIALLY_REFUNDED
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::CANCELED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::PARTIALLY_CANCELLED
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::REFUNDED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::PARTIALLY_CAPTURED
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::CAPTURED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::CALLBACK_AUTHORIZATION
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::CANCELED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::CALLBACK_AUTHORIZATION
            ),
            $this->getEntry(paymentId: $paymentId2, event: Event::CANCELED),
            $this->getEntry(paymentId: $paymentId2, event: Event::CANCELED),
            $this->getEntry(
                paymentId: $paymentId2,
                event: Event::REDIRECTED_TO_GATEWAY
            ),
        ];

        foreach ($entries2 as $entry) {
            $this->handler->write(entry: $entry);
        }

        $this->assertEquals(
            expected: new EntryCollection(data: [
                $entries[0],
                $entries[1],
                $entries[5],
                $entries[6]
            ]),
            actual: $this->handler->getList(
                paymentId: $paymentId1,
                event: Event::REFUNDED
            )
        );

        $this->assertEquals(
            expected: new EntryCollection(data: [
                $entries[2],
                $entries[3],
                $entries[8]
            ]),
            actual: $this->handler->getList(
                paymentId: $paymentId1,
                event: Event::CAPTURED
            )
        );

        $this->assertEquals(
            expected: new EntryCollection(data: [
                $entries[4],
                $entries[7],
                $entries[9],
                $entries[10],
                $entries[11],
                $entries[12],
                $entries[13],
                $entries[14]
            ]),
            actual: $this->handler->getList(
                paymentId: $paymentId1,
                event: Event::CANCELED
            )
        );

        $this->assertEquals(
            expected: new EntryCollection(data: [$entries2[0], $entries2[6]]),
            actual: $this->handler->getList(
                paymentId: $paymentId2,
                event: Event::REFUNDED
            )
        );

        $this->assertEquals(
            expected: new EntryCollection(data: [$entries2[1], $entries2[14]]),
            actual: $this->handler->getList(
                paymentId: $paymentId2,
                event: Event::REDIRECTED_TO_GATEWAY
            )
        );
    }

    /**
     * Assert check to confirm whether an event has (or has not) executed.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testHasExecuted(): void
    {
        $paymentId = Strings::getUuid();
        $entries = [
            $this->getEntry(paymentId: $paymentId, event: Event::REFUNDED),
            $this->getEntry(
                paymentId: $paymentId,
                event: Event::REDIRECTED_TO_GATEWAY
            ),
            $this->getEntry(paymentId: $paymentId, event: Event::CANCELED)
        ];

        foreach ($entries as $entry) {
            $this->handler->write(entry: $entry);
        }

        $this->assertTrue(condition: $this->handler->hasExecuted(
            paymentId: $paymentId,
            event: Event::REFUNDED
        ));
        $this->assertTrue(condition: $this->handler->hasExecuted(
            paymentId: $paymentId,
            event: Event::REDIRECTED_TO_GATEWAY
        ));
        $this->assertFalse(condition: $this->handler->hasExecuted(
            paymentId: $paymentId,
            event: Event::CAPTURED
        ));
        $this->assertFalse(condition: $this->handler->hasExecuted(
            paymentId: $paymentId,
            event: Event::CALLBACK_AUTHORIZATION
        ));
    }
}
