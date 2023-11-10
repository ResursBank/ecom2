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
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Exception\WebhookException;
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
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
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
use Resursbank\Ecom\Lib\Model\Rco\PaymentStatus as RcoPaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\SetStatus;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Method;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Price;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Scope as ShippingScope;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type as ShippingType;
use Resursbank\Ecom\Lib\Model\Rco\Status;
use Resursbank\Ecom\Lib\Model\Rco\Transaction;
use Resursbank\Ecom\Lib\Model\Rco\TransactionCollection;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCheckout;
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
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
                expectedCount: 1,
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
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
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
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
            locale: Locale::sv_SE,
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
                displayName: 'Jocke',
                termsUrl: 'https://example.com'
            )
        ));
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCapture(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8001010001');

        $fetched = Repository::get(id: $validated->id);

        $result = Repository::capture(
            id: $validated->id,
            version: $fetched->version
        );

        $this->assertNotNull(actual: $result->payment);
        $this->assertNotNull(actual: $result->payment->status);
        $this->assertNotNull(actual: $result->payment->status->type);

        $this->assertSame(
            expected: PaymentStatus::CAPTURED,
            actual: $result->payment->status->type
        );
    }

    /**
     * Verify that partial captures work and capture the correct amount.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPartialCapture(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );
        MockSignerRco::approveRco(checkout: $validated, ssn: '8001010001');

        $fetched = Repository::get(id: $validated->id);

        if ($fetched->cart === null) {
            throw new EmptyValueException(
                message: 'Cart missing from response!'
            );
        }

        $fetchedItems = $fetched->cart->items;

        if (
            !$fetchedItems instanceof Cart\ItemCollection ||
            !isset($fetchedItems->toArray()[0])
        ) {
            throw new MissingKeyException(message: 'Cart items not present');
        }

        /** @var Cart\Item $captureItem */
        $captureItem = $fetched->cart->items->toArray()[0];
        $transactionLines = new TransactionCollection(data: [
            new Transaction(
                type: $captureItem->type,
                description: $captureItem->description,
                itemId: $captureItem->itemId,
                quantityUnit: $captureItem->quantityUnit,
                quantity: $captureItem->quantity,
                unitPrice: $captureItem->unitPrice,
                taxRate: $captureItem->taxRate
            )
        ]);

        $result = Repository::capture(
            id: $fetched->id,
            version: $fetched->version,
            transactionLines: $transactionLines
        );

        if ($result->payment === null) {
            throw new EmptyValueException(message: 'Payment object missing!');
        }

        if (!$result->payment->status instanceof RcoPaymentStatus) {
            throw new EmptyValueException(
                message: 'Payment status object missing!'
            );
        }

        $this->assertEquals(
            expected: $captureItem->unitPrice * $captureItem->quantity,
            actual: $result->payment->status->capturedAmount
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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCancel(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8001010001');

        $fetched = Repository::get(id: $validated->id);

        $result = Repository::cancel(
            id: $validated->id,
            version: $fetched->version
        );

        $this->assertNotNull(actual: $result->payment);
        $this->assertNotNull(actual: $result->payment->status);
        $this->assertNotNull(actual: $result->payment->status->type);

        $this->assertSame(
            expected: PaymentStatus::CANCELLED,
            actual: $result->payment->status->type
        );
    }

    /**
     * Assert that full refund refunds the full amount.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRefund(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8001010001');

        $fetched = Repository::get(id: $validated->id);

        $captured = Repository::capture(
            id: $fetched->id,
            version: $fetched->version
        );

        // This get call is required to prevent 409 BadVersion.
        $fetched = Repository::get(id: $validated->id);

        $refunded = Repository::refund(
            id: $captured->id,
            version: $fetched->version
        );

        if (
            $captured->payment?->status === null
        ) {
            throw new EmptyValueException(
                message: 'Missing payment or payment status info on $captured object'
            );
        }

        if (
            $refunded->payment?->status === null
        ) {
            throw new EmptyValueException(
                message: 'Missing payment or payment status info on $refunded object'
            );
        }

        $this->assertEquals(
            expected: $captured->payment->status->capturedAmount,
            actual: $refunded->payment->status->refundedAmount
        );
    }

    /**
     * Assert that partial refunds work as intended.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPartialRefund(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );

        MockSignerRco::approveRco(checkout: $validated, ssn: '8001010001');

        $fetched = Repository::get(id: $validated->id);

        $captured = Repository::capture(
            id: $fetched->id,
            version: $fetched->version
        );

        if ($captured->cart?->items === null) {
            throw new EmptyValueException(message: 'Cart items not present');
        }

        /** @var Cart\Item $cartItem */
        $cartItem = $captured->cart->items->toArray()[0];
        $transactionLines = new TransactionCollection(data: [
            new Transaction(
                type: $cartItem->type,
                description: $cartItem->description,
                itemId: $cartItem->itemId,
                quantityUnit: $cartItem->quantityUnit,
                quantity: $cartItem->quantity,
                unitPrice: $cartItem->unitPrice,
                taxRate: $cartItem->taxRate
            )
        ]);

        $fetched = Repository::get(id: $captured->id);

        $result = Repository::refund(
            id: $captured->id,
            version: $fetched->version,
            transactionLines: $transactionLines
        );

        if ($result->payment?->status === null) {
            throw new EmptyValueException(
                message: 'Missing payment object on $result'
            );
        }

        $this->assertEquals(
            expected: $cartItem->totalPrice,
            actual: $result->payment->status->refundedAmount
        );
    }

    /**
     * Assert that updating a checkout's status works.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUpdate(): void
    {
        $checkout = $this->initFull();
        $fetched = Repository::get(id: $checkout->id);

        $items = [];

        if ($fetched->cart === null) {
            throw new EmptyValueException(
                message: 'No cart object in fetched Checkout'
            );
        }

        /** @var Cart\Item $item */
        foreach ($fetched->cart->items as $item) {
            $items[] = new CreateCart\Item(
                type: $item->type,
                itemId: $item->itemId,
                description: $item->description,
                quantityUnit: $item->quantityUnit,
                unitPrice: $item->unitPrice,
                quantity: $item->quantity,
                taxRate: $item->taxRate,
                totalDiscount: $item->totalDiscount,
                url: $item->url,
                imageUrl: $item->imageUrl,
                tags: $item->tags,
                mutable: $item->mutable
            );
        }

        $cart = new CreateCart(
            items: new CreateCart\ItemCollection(data: $items)
        );

        $newCustomer = new CustomerModel(
            type: $fetched->customer->type,
            governmentId: 'SE8001010001',
            billing: $fetched->customer->billing,
            delivery: $fetched->customer->delivery
        );

        if ($fetched->payment === null) {
            throw new EmptyValueException(
                message: 'No payment object in fetched Checkout'
            );
        }

        $updated = Repository::update(
            id: $fetched->id,
            data: new UpdateCheckout(
                status: new SetStatus(
                    type: CheckoutStatus::VALIDATED,
                    callingIp: '127.0.0.1'
                ),
                selectedPaymentMethodId: $fetched->payment->selection->methodId,
                customer: $newCustomer,
                cart: $cart,
                orderReference: $fetched->orderReference
            ),
            version: $fetched->version
        );

        $this->assertEquals(
            expected: CheckoutStatus::VALIDATED,
            actual: $updated->status->type
        );
    }

    /**
     * Simulate a webhook request.
     *
     * Simulate the body ($_POST) data in an incoming webhook request from the
     * API server and make sure we can parse it into a CheckoutDto instance.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     * @throws WebhookException
     */
    public function testGetWebhookRequestData(): void
    {
        $faultyData = '{"some":"corrupted","data":"set","here":55}';

        try {
            Repository::getWebhookRequestData($faultyData);
            $this->fail(message: 'Invalid webhook data accepted.');
        } catch (WebhookException) {
            $this->addToAssertionCount(count: 1);
        }

        // Simulate a complete CheckoutDto object in $_POST
        $full = json_encode(
            value: $this->initFull(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData($full);
        $this->addToAssertionCount(count: 1);

        // Simulate a minimal CheckoutDto object in $_POST
        $mini = json_encode(
            value: $this->initMini(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData($mini);
        $this->addToAssertionCount(count: 1);
    }

    /**
     * Simulate the processing of a webhook request using the post parameter.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     * @throws WebhookException
     */
    public function testWebhookRequestDataWithPostParameter(): void
    {
        $postData = json_encode(
            value: [
            'some' => 'corrupted',
            'data' => 'set',
            'here' => 55
            ],
            flags: JSON_THROW_ON_ERROR
        );

        try {
            Repository::getWebhookRequestData(post: $postData);
            $this->fail(message: 'Invalid webhook data accepted.');
        } catch (WebhookException) {
            $this->addToAssertionCount(count: 1);
        }

        // Simulate a complete CheckoutDto object in $_POST
        $postData = json_encode(
            value: $this->initFull()->toArray(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData(post: $postData);
        $this->addToAssertionCount(count: 1);

        // Simulate a minimal CheckoutDto object in $_POST
        $postData = json_encode(
            value: $this->initMini()->toArray(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData(post: $postData);
        $this->addToAssertionCount(count: 1);
    }
}
