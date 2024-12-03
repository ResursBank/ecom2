<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\UniqueSellingPoint;
use Throwable;

/**
 * Integration tests for the ReadMore widget.
 */
class UniqueSellingPointTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: new NoneLogger(),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::SV,
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    /**
     * Get list of payment methods, or fail test.
     */
    private function getMethods(): PaymentMethodCollection
    {
        try {
            $methods = Repository::getPaymentMethods();
        } catch (Throwable $e) {
            self::fail($e->getMessage());
        }

        $this->assertNotEmpty($methods);

        return $methods;
    }

    /**
     * Get a UniqueSellingPoint message, or fail test.
     */
    private function getUsp(PaymentMethod $method): string
    {
        try {
            $usp = new UniqueSellingPoint(paymentMethod: $method, amount: 100);

            return $usp->getBasicTranslation($method->type);
        } catch (Throwable $e) {
            self::fail($e->getMessage());
        }
    }

    /**
     * The following payment method type should not have any USP messages.
     *
     * - PAYPAL
     * - MASTERPASS
     * - OTHER
     * - REUSRS_ZERO
     * - RESURS_INVOICE_ACCOUNT
     */
    private function expectEmptyUsp(PaymentMethod $method): bool
    {
        return in_array(
            needle: $method->type->value,
            haystack: [
                'PAYPAL',
                'MASTERPASS',
                'OTHER',
                'RESURS_ZERO',
                'RESURS_INVOICE_ACCOUNT'
            ],
            strict: true
        );
    }

    /**
     * Assert that the getBasicTranslation method returns a string. Indicating
     * that we can translate a payment method type to a USP message using its
     * custom translation file.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function testGetBasicTranslation(): void
    {
        $methods = $this->getMethods();

        // Generate instance of UniqueSellingPoint for each payment methods.
        // Confirm that the getBasicTranslation method returns a string.

        /** @var PaymentMethod $method */
        foreach ($methods as $method) {
            $usp = $this->getUsp(method: $method);

            // Assert that USP is none-empty string (unless expected).
            if (!$this->expectEmptyUsp(method: $method)) {
                $this->assertNotEmpty($usp);
            } else {
                $this->assertEmpty($usp);
            }
        }
    }
}
