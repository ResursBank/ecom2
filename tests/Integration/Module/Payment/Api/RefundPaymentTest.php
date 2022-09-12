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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Models\Payment;

/**
 * Tests for MAPI Payment Refund class
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundPaymentTest extends TestCase
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
     * Create a payment
     *
     * @return Payment
     */
    private function createPayment(): Payment
    {

    }

    /**
     * Verify that refunding an entire payment works
     *
     * @return void
     */
    public function testRefundEntirePayment(): void
    {
        $payment = $this->createPayment();
    }

    /**
     * Verify that refunding a single captured order line works
     *
     * @return void
     */
    public function testRefundSingleOrderLine(): void
    {
        $this->createPayment();
    }

    public function testRefundWithTransactionId(): void
    {
        $this->createPayment();
    }

    public function testRefundWithCreator(): void
    {
        $this->createPayment();
    }
}
