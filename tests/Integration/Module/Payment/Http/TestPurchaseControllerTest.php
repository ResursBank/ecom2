<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Http;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Model\CountryCode;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Payment\Http\TestPurchaseController;

/**
 * Tests for TestPurchaseController.
 */
class TestPurchaseControllerTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup(
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::CREDENTIALS
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Verify basic behavior of performTest.
     *
     * @throws ConfigException
     */
    public function testPerformTest(): void
    {
        $controller = new TestPurchaseController();

        $result = $controller->performTest(countryCode: CountryCode::SE);

        $this->assertTrue(condition: $result['withRefund']['createPayment']);
        $this->assertTrue(condition: $result['withRefund']['capture']);
        $this->assertTrue(condition: $result['withRefund']['refund']);
        $this->assertTrue(condition: $result['withCancel']['createPayment']);
        $this->assertTrue(condition: $result['withCancel']['cancel']);
    }
}
