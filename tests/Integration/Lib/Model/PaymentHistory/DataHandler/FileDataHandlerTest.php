<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Model\PaymentHistory\DataHandler;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\FileDataHandler;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Strings;
use stdClass;

/**
 * Tests for FileDataHandler.
 */
class FileDataHandlerTest extends TestCase
{
    /**
     * Verify basic write and getList functionality.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testWriteAndGetList(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        $entries = [];

        for ($i = 0; $i < 10; $i++) {
            $entries[] = new Entry(
                paymentId: Strings::getUuid(),
                event: Event::CAPTURED,
                user: User::RESURSBANK
            );
        }

        foreach ($entries as $entry) {
            $handler->write(entry: $entry);
        }

        $list = $handler->getList();

        $this->assertNotNull(actual: $list);
        $this->assertCount(
            expectedCount: count($entries),
            haystack: $list
        );

        for ($i = 0; $i < $list->count(); $i++) {
            $this->assertInstanceOf(expected: Entry::class, actual: $list[$i]);
            $this->assertEquals(
                expected: $entries[$i]->paymentId,
                actual: $list[$i]->paymentId
            );
        }

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Verify getList behavior.
     *
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    public function testGetList(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        // Test fetching empty list (should return null).
        $list = $handler->getList();
        $this->assertNull(actual: $list);
    }

    /**
     * Verify hasExecuted behavior.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testHasExecuted(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        // Test for empty list
        $this->assertFalse(
            condition: $handler->hasExecuted(
                paymentId: Strings::getUuid(),
                event: Event::CAPTURED
            )
        );

        // Test for list without sought event.
        $entry = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CAPTURED,
            user: User::RESURSBANK
        );
        $handler->write(entry: $entry);
        $this->assertFalse(
            condition: $handler->hasExecuted(
                paymentId: Strings::getUuid(),
                event: $entry->event
            )
        );

        // Test for list with sought event type
        $this->assertTrue(
            condition: $handler->hasExecuted(
                paymentId: $entry->paymentId,
                event: $entry->event
            )
        );

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Verify filterCollection behavior.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testFilterCollection(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        $collection = new EntryCollection(
            data: [
                new Entry(
                    paymentId: Strings::getUuid(),
                    event: Event::CAPTURED,
                    user: User::RESURSBANK
                )
            ]
        );

        $this->assertEmpty(
            actual: $handler->filterCollection(
                collection: $collection,
                paymentId: Strings::getUuid()
            )
        );

        $entries = [];
        $id = Strings::getUuid();

        for ($i = 0; $i < 10; $i++) {
            $entries[] = new Entry(
                paymentId: $id,
                event: Event::CAPTURED,
                user: User::RESURSBANK
            );
        }

        $entries[] = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CANCELED,
            user: User::RESURSBANK
        );

        $collection = new EntryCollection(data: $entries);

        $this->assertCount(
            expectedCount: count($entries) - 1,
            haystack: $handler->filterCollection(
                collection: $collection,
                paymentId: $id
            )
        );

        foreach ($collection as $entry) {
            $this->assertInstanceOf(expected: Entry::class, actual: $entry);
            $this->assertEquals(expected: $id, actual: $entry->paymentId);
        }

        $entries = [];

        for ($i = 0; $i < 10; $i++) {
            $entries[] = new Entry(
                paymentId: Strings::getUuid(),
                event: Event::CANCELED,
                user: User::RESURSBANK
            );
        }

        $entries[] = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CAPTURED,
            user: User::RESURSBANK
        );

        $collection = new EntryCollection(data: $entries);

        $this->assertCount(
            expectedCount: count($entries) - 1,
            haystack: $handler->filterCollection(
                collection: $collection,
                event: Event::CANCELED
            )
        );

        foreach ($collection as $entry) {
            $this->assertInstanceOf(expected: Entry::class, actual: $entry);
            $this->assertEquals(
                expected: Event::CANCELED,
                actual: $entry->event
            );
        }

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Verify isIdMatch behavior.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIsIdMatch(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        $id = Strings::getUuid();
        $entry = new Entry(
            paymentId: $id,
            event: Event::CAPTURED,
            user: User::RESURSBANK
        );

        $this->assertTrue(
            condition: $handler->isIdMatch(
                entry: $entry,
                paymentId: null
            )
        );

        $this->assertTrue(
            condition: $handler->isIdMatch(
                entry: $entry,
                paymentId: $id
            )
        );

        $this->assertFalse(
            condition: $handler->isIdMatch(
                entry: $entry,
                paymentId: Strings::getUuid()
            )
        );

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Verify isEventMatch behavior.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testIsEventMatch(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (!file_exists(filename: $location)) {
            mkdir(directory: $location, recursive: true);
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        $id = Strings::getUuid();
        $entry = new Entry(
            paymentId: $id,
            event: Event::CAPTURED,
            user: User::RESURSBANK
        );

        $this->assertTrue(
            condition: $handler->isEventMatch(
                entry: $entry,
                event: null
            )
        );
        $this->assertTrue(
            condition: $handler->isEventMatch(
                entry: $entry,
                event: Event::CAPTURED
            )
        );
        $this->assertFalse(
            condition: $handler->isEventMatch(
                entry: $entry,
                event: Event::CANCELED
            )
        );

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Verify getFileContent behavior.
     *
     * @throws AttributeCombinationException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetFileContent(): void
    {
        $location = '/tmp/resursbank/filedatahandlertest';
        $filename = Strings::generateRandomString(length: 12);

        if (
            file_exists(filename: $location . DIRECTORY_SEPARATOR . $filename)
        ) {
            throw new FilesystemException(message: 'File already exists.');
        }

        $handler = new FileDataHandler(
            file: $location . DIRECTORY_SEPARATOR . $filename
        );

        // Test for nonexistent file
        $this->assertEmpty(
            actual: $handler->getFileContent()
        );

        // Test for malformed JSON in file
        mkdir(directory: $location, recursive: true);
        file_put_contents(
            filename: $location . DIRECTORY_SEPARATOR . $filename,
            data: '{{"foo":"bar"}'
        );
        $this->assertEmpty(
            actual: $handler->getFileContent()
        );

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);

        // Test for valid JSON
        $entry = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CAPTURED,
            user: User::RESURSBANK
        );
        $handler->write(entry: $entry);

        $this->assertCount(
            expectedCount: 1,
            haystack: $handler->getFileContent()
        );

        foreach ($handler->getFileContent() as $item) {
            $this->assertInstanceOf(expected: StdClass::class, actual: $item);
        }

        unlink(filename: $location . DIRECTORY_SEPARATOR . $filename);
    }
}
