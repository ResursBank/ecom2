<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Model\PaymentHistory;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * Integrity tests of the PaymentHistory Entry model.
 */
class EntryTest extends TestCase
{
    /**
     * Generate Entry model instance with custom time value.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function getEntry(
        ?int $time = null
    ): Entry {
        return new Entry(
            paymentId: Strings::getUuid(),
            event: Event::REDIRECTED_TO_GATEWAY,
            user: User::RESURSBANK,
            time: $time
        );
    }

    /**
     * Assert the time property defaults to current timestamp.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testTime(): void
    {
        $auto = $this->getEntry();
        $this->assertSame(expected: time(), actual: $auto->time);

        $time = time() - 10000;
        $fixed = $this->getEntry(time: $time);
        $this->assertSame(expected: $time, actual: $fixed->time);
    }
}
