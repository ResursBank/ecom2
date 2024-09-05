<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\UniqueSellingPoint;
use Throwable;

/**
 * Integration tests for the ReadMore widget.
 */
class UniqueSellingPointTest extends TestCase
{
    private PaymentMethod $method;

    private string $url;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: new NoneLogger(),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::SV
        );

        parent::setUp();
    }

    /**
     * Assert that the getBasicTranslation method returns a string. Indicating
     * that we can translate a payment method type to a USP message using its
     * custom translation file.,
     *
     * @return void
     */
    public function testGetBasicTranslation(): void
    {
        // Get payment methods.
        try {
            $methods = Repository::getPaymentMethods(
                storeId: $_ENV['STORE_ID']
            );
        } catch (Throwable $e) {
            self::fail($e->getMessage());
        }

        $this->assertNotEmpty($methods);

        // Generate instance of UniqueSellingPoint for each payment methods.
        // Confirm that the getBasicTranslation method returns a string.
        foreach ($methods as $method) {
            try {
                $usp = new UniqueSellingPoint(
                    paymentMethod: $method,
                    amount: 100
                );
            } catch (Throwable $e) {
                self::fail($e->getMessage());
            }

            try {
                $translation = $usp->getBasicTranslation($method->type);
            } catch (Throwable $e) {
                self::fail($e->getMessage());
            }

            // Assert $translation is not empty, unless the payment method
            // type is one of the following:
            //
            // - PAYPAL
            // - MASTERPASS
            // - OTHER
            // - REUSRS_ZERO
            // - RESURS_INVOICE_ACCOUNT
            if (!in_array($method->type->value, [
                'PAYPAL',
                'MASTERPASS',
                'OTHER',
                'RESURS_ZERO',
                'RESURS_INVOICE_ACCOUNT'
            ])) {
                $this->assertNotEmpty($translation);
            } else {
                $this->assertEmpty($translation);
            }
        }
    }
}
