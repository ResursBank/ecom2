<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Status as RcoStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Module\Payment\Enum\Status;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\Rco\Repository as RcoRepository;
use RuntimeException;

use function sleep;
use function sprintf;

/**
 * Helper to satisfy data integrity tests.
 */
class DataIntegrity
{
    /**
     * Centralized business logic to confirm ValidationException handling for
     * various values.
     */
    public static function testValueIntegrity(
        array $accepted,
        array $rejected,
        callable $callback,
        TestCase $test
    ): void {
        foreach ($accepted as $val) {
            try {
                $callback(v: $val);
                $test->addToAssertionCount(count: 1);
            } catch (IllegalValueException|IllegalCharsetException) {
                $test->fail(message: "Legal value '$val' rejected.");
            }
        }

        foreach ($rejected as $val) {
            try {
                $callback(v: $val);
                $test->fail(message: "Illegal value '$val' accepted.");
            } catch (IllegalValueException|IllegalCharsetException) {
                $test->addToAssertionCount(count: 1);
            }
        }
    }

    /**
     * Centralized business logic to confirm EmptyValueException handling.
     */
    public static function testEmptyValue(
        callable $callback,
        TestCase $test,
        bool $allowed = false
    ): void {
        try {
            $callback();

            if ($allowed) {
                $test->addToAssertionCount(count: 1);
            } else {
                $test->fail(message: 'Empty value accepted.');
            }
        } catch (EmptyValueException) {
            if ($allowed) {
                $test->fail(message: 'Empty value rejected.');
            } else {
                $test->addToAssertionCount(count: 1);
            }
        }
    }
}
