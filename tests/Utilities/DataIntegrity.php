<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use Closure;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use ValueError;

/**
 * Helper to satisfy data integrity tests.
 */
class DataIntegrity
{
    /**
     * Centralized business logic to confirm ValidationException handling for
     * various values.
     *
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function testValueIntegrity(
        array $accepted,
        array $rejected,
        Closure $callback,
        TestCase $test
    ): void {
        self::testAcceptableValues(
            values: $accepted,
            callback: $callback,
            test: $test
        );

        self::testRejectionValues(
            values: $rejected,
            callback: $callback,
            test: $test
        );
    }

    /**
     * Verify that acceptable values won't cause Exceptions/Errors.
     *
     * @throws ReflectionException
     */
    public static function testAcceptableValues(
        array $values,
        Closure $callback,
        TestCase $test
    ): void {
        self::confirmValueIntegrityCallback(callback: $callback, test: $test);

        foreach ($values as $val) {
            try {
                // Ignore next line, it's confirmed by confirmValueIntegrityCallback.
                /* @phpstan-ignore-next-line */
                $callback(v: $val);
                $test->addToAssertionCount(count: 1);
            } catch (IllegalValueException | IllegalCharsetException) {
                $test->fail(message: "Legal value '$val' rejected.");
            }
        }
    }

    /**
     * Verify that illegal values causes Exception/Error.
     *
     * @throws ReflectionException
     */
    public static function testRejectionValues(
        array $values,
        Closure $callback,
        TestCase $test
    ): void {
        self::confirmValueIntegrityCallback(callback: $callback, test: $test);

        foreach ($values as $val) {
            try {
                // Ignore next line, it's confirmed by confirmValueIntegrityCallback.
                /* @phpstan-ignore-next-line */
                $callback(v: $val);
                $test->fail(message: "Illegal value '$val' accepted.");
            } catch (IllegalValueException | IllegalCharsetException | ValueError) {
                $test->addToAssertionCount(count: 1);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function confirmValueIntegrityCallback(
        Closure $callback,
        TestCase $test
    ): void {
        $params = (new ReflectionFunction(
            function: $callback
        ))->getParameters();

        foreach ($params as $param) {
            if ($param->name === 'v') {
                return;
            }
        }

        $test->fail(message: 'Missing parameter v on callback.');
    }

    /**
     * Test empty value is rejected.
     */
    public static function testEmptyValueRejection(
        Closure $callback,
        TestCase $test
    ): void {
        try {
            $callback();
            $test->fail(message: 'Empty value accepted.');
        } catch (EmptyValueException) {
            $test->addToAssertionCount(count: 1);
        }
    }
}
