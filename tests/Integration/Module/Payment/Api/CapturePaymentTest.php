<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Api;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Repository;

/**
 * Tests for MAPI Payment Capture class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CapturePaymentTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: (string)$_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string)$_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string)$_ENV['JWT_AUTH_SCOPE'],
                grantType: (string)$_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );
    }

    /**
     * Verify that capturing an entire order works
     *
     * @return void
     * @throws EmptyValueException
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     */
    public function testCaptureEntirePayment(): void
    {
        // Create payment
        // @todo Create payment when we have a createPayment method available

        // Capture payment
        $response = Repository::capture(paymentId: "064add0e-45d8-46ec-b7a6-2cf0ea7c766e");

        // Assert that payment has been captured in full

    }

    /**
     * Verify that capturing a single specified order line works
     *
     * @return void
     */
    public function testCaptureSingleOrderline(): void
    {
    }

    /**
     * Verify that capturing with a transaction ID works
     *
     * @return void
     */
    public function testCaptureWithTransactionId(): void
    {
    }

    /**
     * Verify that capturing with an invoice ID works
     *
     * @return void
     */
    public function testCaptureWithInvoiceId(): void
    {
    }
}
