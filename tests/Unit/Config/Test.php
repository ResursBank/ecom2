<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Config;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Config\Settings\Defaults;
use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * Unit tests for Defaults configuration class.
 */
class DefaultsTest extends TestCase
{
    public function testGeneralSettings(): void
    {
        $this->assertSame(expected: 30, actual: Defaults::API_TIMEOUT, message: 'API_TIMEOUT should be 30');
        $this->assertTrue(condition: Defaults::CACHE_ENABLED, message: 'CACHE_ENABLED should be true');
        $this->assertTrue(condition: Defaults::GET_ADDRESS_ENABLED, message: 'GET_ADDRESS_ENABLED should be true');
    }

    public function testLoggingSettings(): void
    {
        $this->assertTrue(condition: Defaults::LOG_ENABLED, message: 'LOG_ENABLED should be true');
        $this->assertSame(expected: LogLevel::INFO, actual: Defaults::LOG_LEVEL,
            message: 'LOG_LEVEL should match LogLevel::INFO');
        $this->assertNull(actual: Defaults::XDEBUG_SESSION_VALUE, message: 'XDEBUG_SESSION_VALUE should be null');
    }

    public function testOrderManagementSettings(): void
    {
        $this->assertTrue(condition: Defaults::ORDER_MANAGEMENT_ENABLE_CANCEL,
            message: 'ORDER_MANAGEMENT_ENABLE_CANCEL should be true');
        $this->assertTrue(condition: Defaults::ORDER_MANAGEMENT_ENABLE_CAPTURE,
            message: 'ORDER_MANAGEMENT_ENABLE_CAPTURE should be true');
        $this->assertTrue(condition: Defaults::ORDER_MANAGEMENT_ENABLE_MODIFY,
            message: 'ORDER_MANAGEMENT_ENABLE_MODIFY should be true');
        $this->assertTrue(condition: Defaults::ORDER_MANAGEMENT_ENABLE_REFUND,
            message: 'ORDER_MANAGEMENT_ENABLE_REFUND should be true');
    }

    public function testPartPaymentSettings(): void
    {
        $this->assertSame(expected: 150, actual: Defaults::PART_PAYMENT_LIMIT_NORDIC,
            message: 'PART_PAYMENT_LIMIT_NORDIC should be 150');
        $this->assertSame(expected: 15, actual: Defaults::PART_PAYMENT_LIMIT_EURO,
            message: 'PART_PAYMENT_LIMIT_EURO should be 15');
    }
}
