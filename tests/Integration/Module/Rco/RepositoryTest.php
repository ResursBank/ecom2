<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Callbacks;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Address;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Billing;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Checkbox;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Checkboxes;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Contact;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\CustomerType;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Delivery;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Item;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Options;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Type;
use Resursbank\Ecom\Lib\Model\Rco\Checkout\Webhooks;
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
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function getPayment(): Checkout
    {
        if (!Config::getJwtAuth()) {
            throw new ConfigException(message: 'Missing JWT auth token!');
        }

        $auth = 'Bearer ' . Config::getJwtAuth()->getToken();
        return new Checkout(
            orderReference: 'abc123',
            options: new Options(),
            locale: Locale::SV,
            currency: Currency::SEK,
            cart: new Checkout\Cart(
                code: '',
                items: new ItemCollection(
                    data: [
                        new Item(
                            type: Type::PRODUCT,
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
            customer: new Checkout\Customer(
                type: CustomerType::B2C,
                governmentId: 'SE8305147715',
                billing: new Billing(
                    name: 'John Doe',
                    contact: new Contact(
                        firstName: 'John',
                        lastName: 'Doe',
                        email: 'johndoe@example.com',
                        phone: '+46701234567'
                    ),
                    address: new Address(
                        street: 'Glassgatan 15',
                        addressLine: '',
                        postalCode: '41655',
                        city: 'Göteborg',
                        notes: '',
                        countryCode: CountryCode::SE
                    )
                ),
                delivery: new Delivery(
                    name: 'John Doe',
                    contact: new Contact(
                        firstName: 'John',
                        lastName: 'Doe',
                        email: 'johndoe@example.com',
                        phone: '+46701234567'
                    ),
                    address: new Address(
                        street: 'Glassgatan 15',
                        addressLine: '',
                        postalCode: '41655',
                        city: 'Göteborg',
                        notes: '',
                        countryCode: CountryCode::SE
                    )
                )
            ),
            checkboxes: new Checkboxes(data: [
                new Checkbox(
                    id: 'terms',
                    label: 'Terms and conditions',
                    checked: true,
                    required: true
                )
            ]),
            merchant: new Merchant(
                displayName: 'Resurs Stuff AB',
                logoUrl: 'https://www.example.com/logoUrl.jpg',
                homepageUrl: 'https://www.example.com',
                accessControlAllowOrigin: 'https://www.example.com'
            ),
            callbacks: new Callbacks(
                url: 'https://www.example.com/callbacks',
                authorization: $auth
            ),
            redirects: new Checkout\Redirects(
                success: 'https://www.example.com/success',
                checkout: 'https://www.example.com/checkout'
            ),
            webhooks: $this->getWebhooks(auth: $auth)
        );
    }

    /**
     * Fetch web hooks.
     */
    private function getWebhooks(string $auth): Webhooks
    {
        return new Webhooks(
            customer: new Customer(
                url: 'https://www.example.com/webhooks/customer',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            cart: new Cart(
                url: 'https://www.example.com/webhooks/cart',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            shipping: new Shipping(
                url: 'https://www.example.com/webhooks/shipping',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            payment: new PaymentWebhook(
                url: 'https://wwww.example.com/webhooks/payment',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            validate: new Validate(
                url: 'https://www.example.com/webhooks/validate',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
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
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
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
