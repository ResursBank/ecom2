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
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Api\GetPayment;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\Store\Api\GetStores;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Models\StoreCollection;

class GetPaymentTest extends TestCase
{
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
            ),
            storeId: $this->getStoreId()
        );
    }

    /**
     * Set a store id if phpunit.xml has one (for find_payments).
     * @return string
     */
    private function getStoreId() {
        return (string)($_ENV['MERCHANT_STORE_ID'] ?? '');
    }

    /**
     * This feature will be fixed after findPayments as we need proper payment ids as seen from MAPI.
     * @return void
     * @throws TypeException
     */
    public function testGetPayment()
    {
        // 20220816073146-1557096130 => 9e744903-b9be-431a-a11d-a210f92ecbc3
        $payment = (new GetPayment())->exec('9e744903-b9be-431a-a11d-a210f92ecbc3');

        print_R($payment);
    }
}
