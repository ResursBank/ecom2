<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Config;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Config\Settings\Defaults;
use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * Unit tests for Defaults configuration class.
 *
 * This test suite verifies that all constants in the Defaults class
 * have the expected values and ensures no unexpected changes occur.
 */
class DefaultsTest extends TestCase
{
    /**
     * Test general configuration settings.
     *
     * Ensures that timeout, caching, and address retrieval settings
     * have the expected default values.
     */
    public function testGeneralSettings(): void
    {
        $this->assertSame(expected: 30, actual: Defaults::API_TIMEOUT, message: 'API_TIMEOUT should be 30');
        $this->assertTrue(condition: Defaults::CACHE_ENABLED, message: 'CACHE_ENABLED should be true');
        $this->assertTrue(condition: Defaults::GET_ADDRESS_ENABLED, message: 'GET_ADDRESS_ENABLED should be true');
    }

    /**
     * Test logging configuration settings.
     *
     * Ensures that logging is enabled, log level is set correctly,
     * and the Xdebug session value is null by default.
     */
    public function testLoggingSettings(): void
    {
        $this->assertTrue(condition: Defaults::LOG_ENABLED, message: 'LOG_ENABLED should be true');
        $this->assertSame(
            expected: LogLevel::INFO,
            actual: Defaults::LOG_LEVEL,
            message: 'LOG_LEVEL should match LogLevel::INFO'
        );
        $this->assertNull(actual: Defaults::XDEBUG_SESSION_VALUE, message: 'XDEBUG_SESSION_VALUE should be null');
    }

    /**
     * Test order management settings.
     *
     * Ensures that the default settings for order cancellation, capture,
     * modification, and refunds are enabled.
     */
    public function testOrderManagementSettings(): void
    {
        $this->assertTrue(
            condition: Defaults::ORDER_MANAGEMENT_ENABLE_CANCEL,
            message: 'ORDER_MANAGEMENT_ENABLE_CANCEL should be true'
        );
        $this->assertTrue(
            condition: Defaults::ORDER_MANAGEMENT_ENABLE_CAPTURE,
            message: 'ORDER_MANAGEMENT_ENABLE_CAPTURE should be true'
        );
        $this->assertTrue(
            condition: Defaults::ORDER_MANAGEMENT_ENABLE_MODIFY,
            message: 'ORDER_MANAGEMENT_ENABLE_MODIFY should be true'
        );
        $this->assertTrue(
            condition: Defaults::ORDER_MANAGEMENT_ENABLE_REFUND,
            message: 'ORDER_MANAGEMENT_ENABLE_REFUND should be true'
        );
    }

    /**
     * Test part payment settings.
     *
     * Ensures that the default part payment limits for Nordic countries
     * and Euro-based countries are set correctly.
     */
    public function testPartPaymentSettings(): void
    {
        $this->assertSame(
            expected: 150,
            actual: Defaults::PART_PAYMENT_LIMIT_NORDIC,
            message: 'PART_PAYMENT_LIMIT_NORDIC should be 150'
        );
        $this->assertSame(
            expected: 15,
            actual: Defaults::PART_PAYMENT_LIMIT_EURO,
            message: 'PART_PAYMENT_LIMIT_EURO should be 15'
        );
    }

    /**
     * Ensure that all expected constants are defined.
     */
    public function testAllExpectedConstantsExist(): void
    {
        $expectedConstants = [
            'API_TIMEOUT',
            'CACHE_ENABLED',
            'GET_ADDRESS_ENABLED',
            'LOG_ENABLED',
            'LOG_LEVEL',
            'XDEBUG_SESSION_VALUE',
            'ORDER_MANAGEMENT_ENABLE',
            'ORDER_MANAGEMENT_ENABLE_CANCEL',
            'ORDER_MANAGEMENT_ENABLE_CAPTURE',
            'ORDER_MANAGEMENT_ENABLE_MODIFY',
            'ORDER_MANAGEMENT_ENABLE_REFUND',
            'PART_PAYMENT_ENABLED',
            'PART_PAYMENT_LIMIT_NORDIC',
            'PART_PAYMENT_LIMIT_EURO',
        ];

        foreach ($expectedConstants as $constant) {
            $this->assertTrue(
                condition: defined(constant_name: 'Resursbank\\Ecom\\Lib\\Config\\Settings\\Defaults::' . $constant),
                message: "Constant {$constant} is not defined in Defaults."
            );
        }
    }

    /**
     * Ensure the log level is a valid predefined level.
     */
    public function testLogLevelIsValid(): void
    {
        $validLevels = [LogLevel::INFO, LogLevel::DEBUG, LogLevel::ERROR];
        $this->assertContains(needle: Defaults::LOG_LEVEL, haystack: $validLevels);
    }
}
