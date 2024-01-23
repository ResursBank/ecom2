<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\Repository;

/**
 * Methods relevant for testing of payment history entries.
 */
class PaymentHistory extends TestCase
{
    /**
     * Wrapper to generate Entry model instance.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     * @noinspection PhpTooManyParametersInspection
     */
    // @codingStandardsIgnoreStart
    protected function getEntry(
        ?Result $result = null,
        ?string $extra = null,
        ?string $reference = null,
        ?string $userReference = null,
        ?string $paymentId = null,
        ?Event $event = null
    ): Entry {
        if ($result === null) {
            $result = $this->getRandomResult();
        }

        if ($extra === null) {
            $extra = $this->getRandomExtra(type: $result);
        }

        if ($reference === null) {
            $reference = Random::getString(length: 50);
        }

        if ($userReference === null) {
            $userReference = Random::getString(length: 50);
        }

        if ($paymentId === null) {
            $paymentId = Strings::getUuid();
        }

        if ($event === null) {
            $event = $this->getRandomEvent();
        }

        return new Entry(
            paymentId: $paymentId,
            event: $event,
            user: $this->getRandomUser(),
            result: $result,
            extra: $extra,
            previousOrderStatus: $this->getRandomStatus(),
            currentOrderStatus: $this->getRandomStatus(),
            time: time(),
            userReference: $userReference,
            reference: $reference
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * Resolve collection of log entries.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    protected function getEntries(): EntryCollection
    {
        $data = [];
        $size = rand(min: 1, max: 50);

        for ($i = 0; $i < $size; $i++) {
            $data[] = $this->getEntry();
        }

        return new EntryCollection(data: $data);
    }

    protected function getRandomEvent(): Event
    {
        $cases = Event::cases();
        return $cases[array_rand(array: $cases)];
    }

    protected function getRandomUser(): User
    {
        $cases = User::cases();
        return $cases[array_rand(array: $cases)];
    }

    protected function getRandomResult(): Result
    {
        $cases = Result::cases();
        return $cases[array_rand(array: $cases)];
    }

    /**
     * @throws Exception
     */
    protected function getRandomExtra(Result $type): string
    {
        return match ($type) {
            Result::SUCCESS => '',
            Result::ERROR => Repository::getError(
                error: new TestException(message: 'Spoofed Exception')
            ),
            Result::INFO => Random::getString(
                length: Random::getInt(min: 0, max: 255)
            )
        };
    }

    protected function getRandomStatus(): string
    {
        $statuses = [
            'Pending payment (pending)',
            'Processing (processing)',
            'Closed (closed)',
        ];

        return $statuses[array_rand(array: $statuses)];
    }
}
