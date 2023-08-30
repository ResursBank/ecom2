<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use Closure;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Throwable;
use function is_resource;

/**
 * Helper to satisfy data integrity tests.
 */
class DataIntegrity
{
    /**
     * Mark acceptance test as failed and explain why.
     *
     * @throws JsonException
     */
    private static function failAcceptanceTest(
        mixed $val,
        TestCase $test,
        string $class = '',
        string $parameter = ''
    ): void {
        $message = "Legal value '%s' rejected";

        if ($class !== '' && $parameter !== '') {
            $message .= ' on %s::%s.';
        }

        $test->fail(
            message: sprintf(
                $message,
                is_resource(value: $val) ? 'RESOURCE' : json_encode(
                    value: $val,
                    flags: JSON_THROW_ON_ERROR
                ),
                $class,
                $parameter
            )
        );
    }

    /**
     * Centralized business logic to confirm ValidationException handling for
     * various values.
     *
     * @param string $class Class being probed. For Exception trace.
     * @param string $parameter Parameter being probed. For Exception trace.
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @noinspection PhpTooManyParametersInspection
     */
    public static function testValueIntegrity(
        array $accepted,
        array $rejected,
        Closure $callback,
        TestCase $test,
        string $class = '',
        string $parameter = ''
    ): void {
        self::testAcceptableValues(
            values: $accepted,
            callback: $callback,
            test: $test,
            class: $class,
            parameter: $parameter
        );

        self::testRejectionValues(
            values: $rejected,
            callback: $callback,
            test: $test,
            class: $class,
            parameter: $parameter
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
        TestCase $test,
        string $class = '',
        string $parameter = ''
    ): void {
        self::confirmValueIntegrityCallback(callback: $callback, test: $test);

        foreach ($values as $val) {
            try {
                // Ignore next line, it's confirmed by confirmValueIntegrityCallback.
                /* @phpstan-ignore-next-line */
                $callback(v: $val);
                $test->addToAssertionCount(count: 1);
            } catch (Throwable) {
                self::failAcceptanceTest(
                    val: $val,
                    test: $test,
                    class: $class,
                    parameter: $parameter
                );
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
        TestCase $test,
        string $class = '',
        string $parameter = ''
    ): void {
        self::confirmValueIntegrityCallback(callback: $callback, test: $test);

        $message = "Illegal value '%s' accepted";

        if ($class !== '' && $parameter !== '') {
            $message .= ' on %s::%s.';
        }

        foreach ($values as $val) {
            try {
                // Ignore next line, it's confirmed by confirmValueIntegrityCallback.
                /* @phpstan-ignore-next-line */
                $callback(v: $val);
                $test->fail(
                    message: sprintf(
                        $message,
                        $val ?? 'NULL',
                        $class,
                        $parameter
                    )
                );
            } catch (Throwable) {
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
