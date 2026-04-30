<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\FileDataHandler;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\VoidDataHandler;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\Repository;

/**
 * Integration tests for the PaymentHistory Repository class.
 */
class RepositoryTest extends TestCase
{
    /**
     * Verify that write, getList and hasExecuted use the configured handler.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws IllegalValueException
     */
    public function testWrite(): void
    {
        $directory = '/tmp/resursbank/ecom';

        if (!is_dir(filename: $directory)) {
            mkdir(directory: $directory, recursive: true);
        }

        $filename = $directory . '/payment-history-' .
            Strings::generateRandomString(length: 16) . '.json';
        Config::setup(
            paymentHistoryDataHandler: new FileDataHandler(
                file: $filename
            )
        );

        $paymentId = Strings::getUuid();
        Repository::write(
            new Entry(
                paymentId: $paymentId,
                event: Event::CAPTURED,
                user: User::RESURSBANK
            )
        );

        $this->assertTrue(
            condition: Repository::hasExecuted(
                paymentId: $paymentId,
                event: Event::CAPTURED
            )
        );
        $entries = Repository::getList(paymentId: $paymentId);

        $this->assertNotNull(actual: $entries);
        $this->assertGreaterThan(
            minimum: 0,
            actual: $entries->count()
        );

        if (file_exists(filename: $filename)) {
            unlink(filename: $filename);
        }

        Config::setup(
            paymentHistoryDataHandler: new VoidDataHandler()
        );

        $paymentId = Strings::getUuid();
        Repository::write(
            new Entry(
                paymentId: $paymentId,
                event: Event::CAPTURED,
                user: User::RESURSBANK
            )
        );

        $entries = Repository::getList(paymentId: $paymentId);

        $this->assertNull(actual: $entries);
        $this->assertFalse(
            condition: Repository::hasExecuted(
                paymentId: $paymentId,
                event: Event::CAPTURED
            )
        );
    }
}
