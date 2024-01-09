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
use Resursbank\Ecom\Exception\ValidationException;
use Throwable;
use ValueError;

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
        string $message,
        string $class = '',
        string $parameter = ''
    ): void {
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
     * Centralized business logic.
     *
     * Centralized business logic to confirm ValidationException handling for
     * various values.
     *
     * @param string $class Class being probed. For Exception trace.
     * @param string $parameter Parameter being probed. For Exception trace.
     * @throws ReflectionException|JsonException
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
     * @throws JsonException
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
                    message: "Legal value '%s' rejected",
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
     * @throws JsonException
     */
    public static function testRejectionValues(
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
                self::failAcceptanceTest(
                    val: $val,
                    test: $test,
                    message: "Illegal value '%s' accepted",
                    class: $class,
                    parameter: $parameter
                );
            } catch (ValidationException | ValueError) {
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
