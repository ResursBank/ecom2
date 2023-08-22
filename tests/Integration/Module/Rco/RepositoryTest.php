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
use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Rco;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Rco\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Cart\ItemCollection as CartItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethod;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\Customer as CustomerModel;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Method;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Scope as ShippingScope;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type as ShippingType;
use Resursbank\Ecom\Lib\Model\Rco\Status;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Put;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\EcomTest\Data\Models\Instrument;
use Resursbank\EcomTest\Utilities\MockSignerRco;
use Resursbank\EcomTest\Utilities\Rco as RcoHelper;
use Throwable;

/**
 * Tests for RCO+ module Repository class.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
final class RepositoryTest extends TestCase
{
    private string $orderReference = '';

    /**
     * Set up the Ecom+ config.
     *
     * @throws EmptyValueException
     * @throws Exception
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

        $this->orderReference = Strings::generateRandomString(length: 12);
    }

    /**
     * Perform validation of Checkout, so it gets a payment object attached.
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
    private function validateCheckout(string $id, string $version): Checkout
    {
        $result = (new Put(
            route: Rco::CHECKOUT_ROUTE . '/' . $id,
            version: $version,
            params: [
                'status' => [
                    'type' => 'VALIDATED',
                    'callingIp' => '127.0.0.1'
                ],
                'selectedPaymentMethodId' => $_ENV['RCO_PAYMENT_METHOD_ID']
            ]
        ))->call();

        if (!$result instanceof Checkout) {
            throw new IllegalTypeException(
                message: 'Expected ' . Checkout::class . ', got ' .
                $result::class
            );
        }

        return $result;
    }

    /**
     * Fetch a new payment object.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws UrlValidationException
     * @throws Exception
     */
    private function initFull(
        ?string $orderReference = null
    ): Checkout {
        return Repository::init(request: RcoHelper::getFullCheckout(
            orderReference: $orderReference
        ));
    }

    /**
     * Resolve the smallest possible object to initialize a checkout session.
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
    private function initMini(): Checkout
    {
        return Repository::init(request: RcoHelper::getMiniCheckout());
    }

    /**
     * @throws IllegalTypeException
     */
    private function getShippingMethods(): CreateShippingMethodCollection
    {
        return new CreateShippingMethodCollection(data: [
            new CreateShippingMethod(
                methodId: 'method01',
                name: 'The post',
                scope: [
                    ShippingScope::B2C,
                    ShippingScope::B2B
                ],
                type: ShippingType::MAILBOX,
                carrier: Carrier::POSTNORD,
                description: 'Lorem ipsum',
                price: new Price(
                    display: '49 kr',
                    calculate: 4900,
                    calculateTax: 25
                ),
                deliveryEta: '2 days',
                options: new OptionCollection(data: []),
                required: new RequiredCollection(data: [])
            ),
            new CreateShippingMethod(
                methodId: 'method02',
                name: 'The other post',
                scope: [
                    ShippingScope::B2C,
                    ShippingScope::B2B
                ],
                type: ShippingType::MAILBOX,
                carrier: Carrier::GENERIC,
                description: 'Dolor sit amet',
                price: new Price(
                    display: '79 kr',
                    calculate: 7900,
                    calculateTax: 25
                ),
                deliveryEta: '1 day',
                options: new OptionCollection(data: []),
                required: new RequiredCollection(data: [])
            )
        ]);
    }

    /**
     * Assert that a minimal Checkout init call works.
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
    public function testMinimalInit(): void
    {
        $checkout = $this->initMini();

        $this->assertNotNull(actual: $checkout->id);
    }

    /**
     * Assert that Init returns a Payment object.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws UrlValidationException
     * @throws Exception
     * @todo Expand this to not just test the orderReference.
     */
    public function testInit(): void
    {
        $orderReference = Strings::generateRandomString(length: 12);

        $checkout = $this->initFull(orderReference: $orderReference);

        $this->assertEquals(
            expected: $orderReference,
            actual: $checkout->orderReference
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
     * @throws UrlValidationException
     */
    public function testSetCart(): void
    {
        $checkout = $this->initFull();
        $newCart = RcoHelper::getCart();
        $result = Repository::setCart(
            id: $checkout->id,
            cart: $newCart,
            version: $checkout->version
        );

        $this->assertNotNull(actual: $result->cart);

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
     * @throws UrlValidationException
     */
    public function testPatchCart(): void
    {
        $checkout = $this->initFull();

        $this->assertNotNull(actual: $checkout->cart);

        $newQty = 8;
        $result = Repository::patchCart(
            id: $checkout->id,
            itemId: $checkout->cart->items->toArray()[0]->itemId,
            version: $checkout->version,
            quantity: $newQty
        );

        $this->assertNotNull(actual: $result->cart);

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
     * @throws UrlValidationException
     */
    public function testSetShippingMethods(): void
    {
        $checkout = $this->initFull();

        $shippingMethods = $this->getShippingMethods();

        $result = Repository::setShippingMethods(
            id: $checkout->id,
            shippingMethods: $shippingMethods,
            version: $checkout->version
        );

        if ($result->shipping === null) {
            throw new IllegalValueException(
                message: 'Property "shipping" missing from setShippingMethods' .
                ' response'
            );
        }

        $fetchedMethods = $result->shipping->methods;

        $this->assertCount(
            expectedCount: count($shippingMethods),
            haystack: $fetchedMethods
        );

        /** @var Method $shippingMethod */
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
     * @throws UrlValidationException
     */
    public function testDeleteCartItem(): void
    {
        $checkout = $this->initFull();

        $this->assertNotNull(actual: $checkout->cart);

        $result = Repository::deleteCartItem(
            id: $checkout->id,
            itemId: $checkout->cart->items->toArray()[0]->itemId,
            version: $checkout->version
        );

        if ($result->cart !== null) {
            // When successfully deleting a single item in a cart that only contains one item,
            // the final result is still iterable - not null. If cart object is null, something went wrong
            // in the API and this test should fail.
            $this->assertCount(
                expectedCount: 0,
                haystack: $result->cart->items
            );
            return;
        }

        $this->fail(message: 'Cart returned as null.');
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
     * @throws UrlValidationException
     * @throws Exception
     */
    public function testSetOrderReference(): void
    {
        $checkout = $this->initFull();

        $orderReference = Strings::generateRandomString(length: 16);
        $result = Repository::setOrderReference(
            id: $checkout->id,
            orderReference: $orderReference,
            version: $checkout->version
        );

        $this->assertEquals(
            expected: $orderReference,
            actual: $result->orderReference
        );
    }

    /**
     * Assert that fetching a checkout works.
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
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testGet(): void
    {
        $init = $this->initFull();
        $fetched = Repository::get(id: $init->id);

        $this->assertEquals(expected: $init->id, actual: $fetched->id);
        $this->assertSame(
            expected: CheckoutStatus::CREATED,
            actual: $fetched->status->type
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws IllegalCharsetException
     */
    public function testValidateCheckoutModel(): void
    {
        try {
            Repository::validateCheckoutModel(model: new Instrument(
                id: 10,
                name: 'anka'
            ));

            $this->fail(
                message: 'Validation failed to confirm model is instance of ' . Checkout::class
            );
        } catch (Throwable) {
            $this->addToAssertionCount(count: 1);
        }

        Repository::validateCheckoutModel(model: new Checkout(
            id: Strings::getUuid(),
            storeId: Strings::getUuid(),
            orderReference: $this->orderReference,
            countryCode: CountryCode::SE,
            locale: Locale::SV,
            currency: Currency::SEK,
            version: Strings::getUuid(),
            options: new Options(),
            customer: new CustomerModel(
                type: Type::B2C
            ),
            status: new Status(type: CheckoutStatus::CREATED),
            cart: new Cart(
                items: new CartItemCollection(data: []),
                code: 'nothing'
            ),
            merchant: new Merchant(
                displayName: 'Jocke'
            )
        ));
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testCapture(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8305147715');

        $result = Repository::capture(
            id: $validated->id,
            version: $validated->version
        );

        $this->assertNotNull(actual: $result->payment);
        $this->assertNotNull(actual: $result->payment->paymentStatus);

        $this->assertSame(
            expected: PaymentStatus::CAPTURED,
            actual: $result->payment->paymentStatus->status
        );
    }

    /**
     * Assert that cancelling a payment works as intended.
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
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testCancel(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8305147715');

        $fetched = Repository::get(id: $validated->id);

        $result = Repository::cancel(
            id: $validated->id,
            version: $fetched->version
        );

        $this->assertNotNull(actual: $result->payment);
        $this->assertNotNull(actual: $result->payment->paymentStatus);

        $this->assertSame(
            expected: PaymentStatus::CANCELLED,
            actual: $result->payment->paymentStatus->status
        );
    }
}
