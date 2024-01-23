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
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    protected function getRandomUser(): User
    {
        $cases = User::cases();
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    protected function getRandomResult(): Result
    {
        $cases = Result::cases();
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    protected function getRandomExtra(Result $type): string
    {
        // If $type is SUCCESS, return an empty string.
        if ($type === Result::SUCCESS) {
            return '';
        }

        // If $type is ERROR, create a spoofed Exception and return its trace.
        if ($type === Result::ERROR) {
            $exception = new TestException(message: 'Spoofed Exception');
            return $exception->getTraceAsString();
        }

        // For INFO type.
        $maxLength = 2400;

        $words = [
            'Lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
            'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
            'magna', 'aliqua', 'Ut', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
            'exercitation', 'ullamco', 'laboris', 'nisi', 'ut', 'aliquip', 'ex', 'ea',
            'commodo', 'consequat', 'Duis', 'aute', 'irure', 'dolor', 'in', 'reprehenderit',
            'in', 'voluptate', 'velit', 'esse', 'cillum', 'dolore', 'eu', 'fugiat', 'nulla',
            'pariatur', 'Excepteur', 'sint', 'occaecat', 'cupidatat', 'non', 'proident', 'sunt',
            'in', 'culpa', 'qui', 'officia', 'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum'
        ];

        // Generate and return the lorem ipsum text.
        $loremIpsumText = '';

        while (strlen(string: $loremIpsumText) < $maxLength) {
            /* @phpstan-ignore-next-line */
            $randomWord = $words[array_rand(array: $words)];
            $loremIpsumText .= ' ' . $randomWord;
        }

        return substr(string: $loremIpsumText, offset: 0, length: $maxLength);
    }

    protected function getRandomStatus(): string
    {
        $statuses = [
            'Pending payment (pending)',
            'Processing (processing)',
            'Closed (closed)',
        ];

        /* @phpstan-ignore-next-line */
        return $statuses[array_rand(array: $statuses)];
    }
}
