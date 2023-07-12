<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco;

use Exception;
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
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Scope as ShippingScope;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethod;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\ShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type as ShippingType;
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
     * Generates a random string of characters.
     *
     * @throws Exception
     */
    private function generateOrderReference(int $length): string
    {
        return bin2hex(string: random_bytes(length: max(1, $length)));
    }

    /**
     * Fetch a new payment object.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function getCheckout(): Checkout
    {
        if (!Config::getJwtAuth()) {
            throw new ConfigException(message: 'Missing JWT auth token!');
        }

        $auth = 'Bearer ' . Config::getJwtAuth()->getToken();
        return new Checkout(
            orderReference: 'abc123',
            options: new Options(
                mutableCart: true
            ),
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
                            url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '',
                            imageUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/image.jpg'
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
                logoUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/logoUrl.jpg',
                homepageUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'],
                accessControlAllowOrigin: $_ENV['RCOPLUS_HOMEPAGE_URL']
            ),
            callbacks: new Callbacks(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/callbacks',
                authorization: $auth
            ),
            redirects: new Checkout\Redirects(
                success: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/success',
                checkout: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/checkout'
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
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/customer',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            cart: new Cart(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/cart',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            shipping: new Shipping(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/shipping',
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
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/validate',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            )
        );
    }

    /**
     * Generate a different cart from the one from getCheckout.
     *
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private function getCart(): Checkout\Cart
    {
        return new Checkout\Cart(
            code: '',
            items: new ItemCollection(
                data: [
                    new Item(
                        type: Type::PRODUCT,
                        itemId: 'item02',
                        description: 'Another Item',
                        quantityUnit: 'st',
                        quantity: 2,
                        unitPrice: 1500,
                        taxRate: 25,
                        totalDiscount: 0,
                        url: 'https://www.example.com',
                        imageUrl: 'https://www.example.com/image.jpg'
                    )
                ]
            )
        );
    }

    private function getShippingMethods(): ShippingMethodCollection
    {
        return new ShippingMethodCollection(data: [
            new ShippingMethod(
                methodId: 'method01',
                name: 'The post',
                scope: [ShippingScope::B2C],
                type: ShippingType::MAILBOX,
                description: 'Lorem ipsum',
                price: new Price(
                    display: '49 kr',
                    calculate: 4900,
                    calculateTax: 25
                ),
                deliveryEta: '2 days',
                options: [],
                required: [],
                carrier: Carrier::POSTNORD
            ),
            new ShippingMethod(
                methodId: 'method02',
                name: 'The other post',
                scope: [ShippingScope::B2C],
                type: ShippingType::MAILBOX,
                description: 'Dolor sit amet',
                price: new Price(
                    display: '79 kr',
                    calculate: 7900,
                    calculateTax: 25
                ),
                deliveryEta: '1 day',
                options: [],
                required: [],
                carrier: Carrier::GENERIC
            )
        ]);
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
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        $this->assertEquals(
            expected: $request->orderReference,
            actual: $response->orderReference
        );
    }

    /**
     * Assert that setCart properly updates the cart.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSetCart(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        if (!$response->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        if (!$response->version) {
            throw new IllegalValueException(
                message: 'Property "version" missing from Init response.'
            );
        }

        $newCart = $this->getCart();
        $result = Repository::setCart(
            id: $response->id,
            cart: $newCart,
            version: $response->version
        );

        $items = $result->cart->items->toArray();


        $this->assertEquals(
            expected: 1,
            actual: sizeof($items)
        );
        $this->assertEquals(
            expected: $newCart->items->toArray()[0]->itemId,
            actual: $items[0]->itemId
        );
    }

    /**
     * Assert that changing the quantity of an item works.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPatchCart(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        if (!$response->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        if (!$response->version) {
            throw new IllegalValueException(
                message: 'Property "version" missing from Init response.'
            );
        }

        $newQty = 8;
        $result = Repository::patchCart(
            id: $response->id,
            itemId: $response->cart->items->toArray()[0]->itemId,
            version: $response->version,
            quantity: $newQty
        );

        $this->assertEquals(
            expected: $newQty,
            actual: $result->cart->items->toArray()[0]->quantity
        );
    }

    /**
     * Assert that setting shipping methods actually sets them.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSetShippingMethods(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        if (!$response->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        if (!$response->version) {
            throw new IllegalValueException(
                message: 'Property "version" missing from Init response.'
            );
        }

        $shippingMethods = $this->getShippingMethods();

        $result = Repository::setShippingMethods(
            id: $response->id,
            shippingMethods: $shippingMethods,
            version: $response->version
        );

        if (!$result->shipping) {
            throw new IllegalValueException(
                message: 'Property "shipping" missing from setShippingMethods' .
                ' response'
            );
        }

        if (!$result->shipping->methods) {
            throw new IllegalValueException(
                message: 'Property "methods" missing from setShippingMethods' .
                ' response'
            );
        }

        $fetchedMethods = $result->shipping->methods;

        $this->assertCount(
            expectedCount: count($shippingMethods),
            haystack: $fetchedMethods
        );

        /** @var ShippingMethod $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            $this->assertTrue(
                condition: $fetchedMethods->hasObjectWithPropertyValue(
                    propertyName: 'methodId',
                    propertyValue: $shippingMethod->methodId
                )
            );
        }
    }

    /**
     * Assert that the deleteCartItem method removes cart items.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDeleteCartItem(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        if (!$response->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        if (!$response->version) {
            throw new IllegalValueException(
                message: 'Property "version" missing from Init response.'
            );
        }

        $result = Repository::deleteCartItem(
            id: $response->id,
            itemId: $response->cart->items->toArray()[0]->itemId,
            version: $response->version
        );

        $this->assertEquals(
            expected: 0,
            actual: sizeof($result->cart->items->toArray())
        );
    }

    /**
     * Assert that order reference is properly set.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSetOrderReference(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        if (!$response->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        if (!$response->version) {
            throw new IllegalValueException(
                message: 'Property "version" missing from Init response.'
            );
        }

        $orderReference = $this->generateOrderReference(length: 16);
        $result = Repository::setOrderReference(
            id: $response->id,
            orderReference: $orderReference,
            version: $response->version
        );

        $this->assertEquals(
            expected: $orderReference,
            actual: $result->orderReference
        );
    }

    public function testGet(): void
    {
        $request = $this->getCheckout();
        $response = Repository::init(checkout: $request);

        $fetched = Repository::get(id: $response->id);

        $this->assertEquals(expected: 1, actual: 2);
    }
}
