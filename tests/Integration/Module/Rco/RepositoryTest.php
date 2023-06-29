<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Callbacks;
use Resursbank\Ecom\Lib\Model\Rco\Payment;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\Payment as PaymentWebhook;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\Shipping;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks\Validate;
use Resursbank\Ecom\Module\Rco\Repository;

/**
 * Tests for RCO+ module Repository class.
 */
final class RepositoryTest extends TestCase
{
    /**
     * Set up the Ecom+ config.
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
     * Fetch a new payment object.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ConfigException
     */
    private function getPayment(): Payment
    {
        if (!Config::getJwtAuth()) {
            throw new ConfigException(message: 'Missing JWT auth token!');
        }

        $auth = 'Bearer ' . Config::getJwtAuth()->getToken();
        return new Payment(
            options: new Payment\Options(),
            orderReference: 'abc123',
            locale: Locale::SV,
            currency: Payment\Currency::SEK,
            cart: new Payment\Cart(
                code: '',
                items: new Payment\ItemCollection(
                    data: [
                        new Payment\Item(
                            type: Payment\Type::PRODUCT,
                            itemId: 'item01',
                            description: 'An Item',
                            quantityUnit: 'st',
                            quantity: 1,
                            unitPrice: 1000,
                            taxRate: 25,
                            totalDiscount: 0,
                            url: 'https://www.example.com',
                            imageUrl: 'https://www.example.com/image.jpg'
                        )
                    ]
                )
            ),
            customer: new Payment\Customer(
                delivery: new Payment\Delivery(
                    name: 'John Doe',
                    contact: new Payment\Contact(
                        firstName: 'John',
                        lastName: 'Doe',
                        email: 'johndoe@example.com',
                        phone: '+46701234567'
                    ),
                    address: new Payment\Address(
                        street: 'Glassgatan 15',
                        addressLine: '',
                        postalCode: '41655',
                        city: 'Göteborg',
                        countryCode: Payment\CountryCode::SE,
                        notes: ''
                    )
                ),
                type: Payment\CustomerType::B2C,
                governmentId: 'SE8305147715',
                billing: new Payment\Billing(
                    name: 'John Doe',
                    contact: new Payment\Contact(
                        firstName: 'John',
                        lastName: 'Doe',
                        email: 'johndoe@example.com',
                        phone: '+46701234567'
                    ),
                    address: new Payment\Address(
                        street: 'Glassgatan 15',
                        addressLine: '',
                        postalCode: '41655',
                        city: 'Göteborg',
                        countryCode: Payment\CountryCode::SE,
                        notes: ''
                    )
                )
            ),
            redirects: new Payment\Redirects(
                success: 'https://www.example.com/success',
                checkout: 'https://www.example.com/checkout'
            ),
            callbacks: new Callbacks(
                url: 'https://www.example.com/callbacks',
                authorization: $auth
            ),
            webhooks: new Payment\Webhooks(
                customer: new Customer(
                    url: 'https://www.example.com/webhooks/customer',
                    authorization: $auth,
                    timeout: 60,
                    continueOnNoResponse: true
                ),
                cart: new Cart(
                    url: 'https://www.example.com/webhooks/cart',
                    authorization: $auth,
                    timeout: 60,
                    continueOnNoResponse: true
                ),
                shipping: new Shipping(
                    url: 'https://www.example.com/webhooks/shipping',
                    authorization: $auth,
                    timeout: 60,
                    continueOnNoResponse: true
                ),
                payment: new PaymentWebhook(
                    url: 'https://wwww.example.com/webhooks/payment',
                    authorization: $auth,
                    timeout: 60,
                    continueOnNoResponse: true
                ),
                validate: new Validate(
                    url: 'https://www.example.com/webhooks/validate',
                    authorization: $auth,
                    timeout: 60,
                    continueOnNoResponse: true
                )
            ),
            checkboxes: new Payment\Checkboxes(data: [
                new Payment\Checkbox(
                    id: 'terms',
                    label: 'Terms and conditions',
                    checked: true,
                    required: true
                )
            ]),
            merchant: new Payment\Merchant(
                displayName: 'Resurs Stuff AB',
                logoUrl: 'https://www.example.com/logoUrl.jpg',
                homepageUrl: 'https://www.example.com',
                accessControlAllowOrigin: 'https://www.example.com'
            )
        );
    }

    /**
     * Assert that Init returns a Payment object.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @todo Expand this to not just test the orderReference.
     */
    public function testInit(): void
    {
        $request = $this->getPayment();
        $response = Repository::init(payment: $request);

        $this->assertEquals(
            expected: $request->orderReference,
            actual: $response->orderReference
        );
    }
}
