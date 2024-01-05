<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory\DataHandler;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Status;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\DataHandler\FileDataHandler;

/**
 * Integration test to confirm functionality of bundled payment history
 * storage class.
 */
class FileDataHandlerTest extends TestCase
{
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
     * Assert write functionality works by reading file contents back manually,
     * decoding it, and ensuring data integrity is maintained.
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
        $handler = new FileDataHandler(file: $this->testFilePath);

        $entry = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CAPTURE_REQUESTED,
            user: User::ADMIN,
            status: Status::INFO,
            extra: 'extra data',
            previousOrderStatus: 'previous status 1',
            currentOrderStatus: 'current status 1'
        );
        $handler->write(entry: $entry);

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
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     * @throws JsonException
     * @throws AttributeCombinationException
     * @throws CollectionException
     */
    public function testGetList(): void
    {
        $handler = new FileDataHandler(file: $this->testFilePath);

        /* Write single entry to file, get it back from the file, make sure data
           is intact. */
        $entry1 = new Entry(
            paymentId: Strings::getUuid(),
            event: Event::CAPTURE_REQUESTED,
            user: User::ADMIN,
            status: Status::INFO,
            extra: 'extra data',
            previousOrderStatus: 'previous status 1',
            currentOrderStatus: 'current status 1'
        );
        $handler->write(entry: $entry1);

        $collection1 = $handler->getList(paymentId: $entry1->paymentId);
        $this->assertInstanceOf(
            expected: EntryCollection::class,
            actual: $collection1
        );
        $this->assertCount(expectedCount: 1, haystack: $collection1);
        $this->assertEquals(
            expected: $entry1,
            actual: $collection1->current()
        );

        /* Write multiple entries to the file, pick up only the new entries
           ignoring the one from our previous tests above. This ensures
           filtering by paymentId works as expected. */
        $paymentId = Strings::getUuid();

        $entry2 = new Entry(
            paymentId: $paymentId,
            event: Event::CAPTURED,
            user: User::ADMIN,
            status: Status::SUCCESS
        );
        $handler->write(entry: $entry2);

        $entry3 = new Entry(
            paymentId: $paymentId,
            event: Event::CANCELLED,
            user: User::ADMIN,
            status: Status::ERROR,
            extra: (string) json_encode(
                value: (new TestException(message: 'test'))->getTrace()
            )
        );
        $handler->write(entry: $entry3);

        $collection2 = $handler->getList(paymentId: $paymentId);
        $this->assertInstanceOf(
            expected: EntryCollection::class,
            actual: $collection2
        );
        $this->assertCount(expectedCount: 2, haystack: $collection2);
        $this->assertEquals(
            expected: $collection2,
            actual: new EntryCollection(data: [$entry2, $entry3])
        );
    }
}
