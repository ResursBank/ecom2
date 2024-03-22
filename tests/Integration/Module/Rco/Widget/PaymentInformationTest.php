<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco\Widget;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\Ecom\Module\Rco\Widget\PaymentInformation;
use Resursbank\EcomTest\Utilities\Rco;

/**
 * Tests for the payment information widget.
 */
class PaymentInformationTest extends TestCase
{
    /**
     * Establish API connection.
     *
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: new NoneLogger(),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['RCO_JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['RCO_JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['RCO_JWT_AUTH_SCOPE']),
                grantType: GrantType::from(
                    value: $_ENV['RCO_JWT_AUTH_GRANT_TYPE']
                )
            )
        );
    }

    /**
     * Verify that widget renders
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testRenderWidget(): void
    {
        $checkout = Repository::init(request: Rco::getFullCheckout());

        if (!$checkout->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        $widget = new PaymentInformation(
            paymentId: $checkout->id,
            currencySymbol: 'kr',
            currencyFormat: CurrencyFormat::SYMBOL_LAST
        );

        $this->assertEquals(
            expected: $checkout->id,
            actual: $widget->checkout->id,
            message: 'Widget payment id does not match original payment id'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>{$widget->checkout->id}<\/td>/s",
            string: $widget->content,
            message: 'Widget does not contain payment id cell.'
        );
    }
}
