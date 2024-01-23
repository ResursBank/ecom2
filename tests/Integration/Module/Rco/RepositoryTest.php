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
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Cart;
use Resursbank\Ecom\Lib\Model\Rco\Cart\ItemCollection as CartItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\CheckboxCollection;
use Resursbank\Ecom\Lib\Model\Rco\Checkout;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Model\Rco\CreateAddress;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateRecipient;
use Resursbank\Ecom\Lib\Model\Rco\CreateContact;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethod;
use Resursbank\Ecom\Lib\Model\Rco\CreateShippingMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransaction;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLine;
use Resursbank\Ecom\Lib\Model\Rco\CreateTransactionLineCollection;
use Resursbank\Ecom\Lib\Model\Rco\Customer as CustomerModel;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActionsCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentSelection as PaymentSelectionType;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\RequiredCollection;
use Resursbank\Ecom\Lib\Model\Rco\Enum\ShippingSelection;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Options;
use Resursbank\Ecom\Lib\Model\Rco\Payment;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentSelection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentStatus as RcoPaymentStatus;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Model\Rco\SetStatus;
use Resursbank\Ecom\Lib\Model\Rco\Shipping;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Carrier;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Method;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\OptionCollection;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Scope as ShippingScope;
use Resursbank\Ecom\Lib\Model\Rco\Shipping\Type as ShippingType;
use Resursbank\Ecom\Lib\Model\Rco\Status;
use Resursbank\Ecom\Lib\Model\Rco\Tracking;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCheckout;
use Resursbank\Ecom\Lib\Model\Rco\UpdateCustomer;
use Resursbank\Ecom\Lib\Repository\Api\Rco\Put;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\DataHandler\FileDataHandler;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\EcomTest\Data\Models\Instrument;
use Resursbank\EcomTest\Utilities\MockSignerRco;
use Resursbank\EcomTest\Utilities\Rco as RcoHelper;
use Throwable;

/**
 * Tests for RCO+ module Repository class.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @noinspection EfferentObjectCouplingInspection
 */
final class RepositoryTest extends TestCase
{
    private string $orderReference = '';
    private string $historyFile = '/tmp/resursbank/test/rco/payment-history.log';

    /**
     * Set up the Ecom+ config.
     *
     * @throws EmptyValueException
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (!is_dir(filename: dirname(path: $this->historyFile))) {
            mkdir(
                directory: dirname(path: $this->historyFile),
                permissions: 0755,
                recursive: true
            );
        }

        if (!is_file(filename: $this->historyFile)) {
            touch(filename: $this->historyFile);
        }

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
            ),
            paymentHistoryDataHandler: new FileDataHandler(
                file: $this->historyFile
            )
        );

        $this->orderReference = Strings::generateRandomString(length: 12);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (is_file(filename: $this->historyFile)) {
            unlink(filename: $this->historyFile);
        }

        parent::tearDown();
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
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
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
                price: new CreateShippingMethod\Price(
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
                price: new CreateShippingMethod\Price(
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
    public function testSetShippingMethods(): void
    {
        $checkout = $this->initFull();

        $shippingMethods = $this->getShippingMethods();

        $result = Repository::setShippingMethods(
            id: $checkout->id,
            shippingMethods: $shippingMethods,
            version: $checkout->version
        );

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

        $originalItemCount = count(value: $checkout->cart->items);

        $result = Repository::deleteCartItem(
            id: $checkout->id,
            itemId: $checkout->cart->items->toArray()[0]->itemId,
            version: $checkout->version
        );

        $this->assertCount(
            expectedCount: $originalItemCount - 1,
            haystack: $result->cart->items
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
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $shippingSelection = new Shipping\Selection(
            methodId: Strings::getUuid(),
            optionId: Strings::getUuid(),
            type: ShippingSelection::DEFAULT
        );

        Repository::validateCheckoutModel(model: new Checkout(
            id: Strings::getUuid(),
            storeId: Strings::getUuid(),
            orderReference: $this->orderReference,
            countryCode: CountryCode::SE,
            locale: Locale::sv_SE,
            currency: Currency::SEK,
            version: Strings::getUuid(),
            options: new Options(
                renderCart: true,
                calculateShipping: true,
                lookupB2CAddress: true,
                renderCartCode: true,
                renderNotes: true,
                allowDelayedAuthorization: true
            ),
            checkboxes: new CheckboxCollection(data: [
            ]),
            notes: '',
            customer: new CustomerModel(
                type: Type::B2C,
                delivery: new Recipient(
                    name: Strings::generateRandomString(length: 12),
                    contact: new Contact(
                        firstName: Strings::generateRandomString(length: 32),
                        lastName: Strings::generateRandomString(length: 32),
                        phone: '+46701234567',
                        email: Strings::generateRandomString(length: 32)
                    ),
                    address: new Address()
                ),
                billing: new Recipient(
                    name: Strings::generateRandomString(length: 12),
                    contact: new Contact(
                        firstName: Strings::generateRandomString(length: 32),
                        lastName: Strings::generateRandomString(length: 32),
                        phone: '+46701234567',
                        email: Strings::generateRandomString(length: 32)
                    ),
                    address: new Address()
                ),
                governmentId: 'SE' . $_ENV['RCO_JWT_GOVERNMENT_ID']
            ),
            shipping: new Shipping(
                tracking: new Tracking(
                    url: 'https://example.com'
                ),
                selection: $shippingSelection,
                methods: new Shipping\MethodCollection(data: [
                    new Method(
                        methodId: $shippingSelection->methodId,
                        description: Strings::generateRandomString(length: 12),
                        type: Shipping\Type::DELIVERY,
                        carrier: Carrier::GENERIC,
                        name: Strings::generateRandomString(length: 12),
                        deliveryEta: Strings::generateRandomString(length: 12),
                        price: new Shipping\Price(
                            display: '5,00',
                            calculateTax: 100,
                            calculate: 500
                        ),
                        options: new OptionCollection(data: []),
                        required: new RequiredCollection(data: [])
                    )
                ])
            ),
            payment: new Payment(
                methods: new PaymentMethodCollection(data: [
                ]),
                selection: new PaymentSelection(
                    methodId: Strings::getUuid(),
                    type: PaymentSelectionType::DEFAULT
                ),
                status: new RcoPaymentStatus(
                    requestedAmount: 1000,
                    authorizedAmount: 1000,
                    cancelledAmount: 0,
                    capturedAmount: 0,
                    refundedAmount: 0,
                    availableActions: new AvailableActionsCollection(data: [
                        AvailableActions::CAPTURE,
                        AvailableActions::CANCEL
                    ]),
                    type: PaymentStatus::AUTHORIZED
                )
            ),
            status: new Status(type: CheckoutStatus::CREATED),
            cart: new Cart(
                items: new CartItemCollection(data: []),
                code: 'nothing'
            ),
            merchant: new Merchant(
                displayName: 'Jocke',
                termsUrl: 'https://example.com',
                homepageUrl: 'https://example.com',
                logoUrl: 'https://example.com'
            )
        ));
    }

    /**
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testCapture(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(
            checkout: $validated,
            ssn: $_ENV['RCO_JWT_GOVERNMENT_ID']
        );

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

        // Confirm events are tracked by payment history.
        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CAPTURE_REQUESTED
            )
        );

        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CAPTURED
            )
        );

        $this->assertFalse(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::PARTIALLY_CAPTURED
            )
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testPartialCapture(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );
        MockSignerRco::approveRco(
            checkout: $validated,
            ssn: $_ENV['RCO_JWT_GOVERNMENT_ID']
        );

        $fetched = Repository::get(id: $validated->id);

        $fetchedItems = $fetched->cart->items;

        if (
            !$fetchedItems instanceof Cart\ItemCollection ||
            !isset($fetchedItems->toArray()[0])
        ) {
            throw new MissingKeyException(message: 'Cart items not present');
        }

        /** @var Cart\Item $captureItem */
        $captureItem = $fetched->cart->items->toArray()[0];
        $transactionLines = new CreateTransactionLineCollection(data: [
            new CreateTransactionLine(
                type: $captureItem->type,
                description: $captureItem->description,
                itemId: $captureItem->itemId,
                itemIdDisplay: $captureItem->itemIdDisplay,
                quantityUnit: $captureItem->quantityUnit,
                quantity: $captureItem->quantity,
                unitPrice: $captureItem->unitPrice,
                taxRate: $captureItem->taxRate
            )
        ]);

        $result = Repository::capture(
            id: $fetched->id,
            version: $fetched->version,
            createTransaction: new CreateTransaction(
                transactionLines: $transactionLines
            )
        );

        if (!$result->payment->status instanceof RcoPaymentStatus) {
            throw new EmptyValueException(
                message: 'Payment status object missing!'
            );
        }

        $this->assertEquals(
            expected: $captureItem->unitPrice * $captureItem->quantity,
            actual: $result->payment->status->capturedAmount
        );

        // Confirm events are tracked by payment history.
        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CAPTURE_REQUESTED
            )
        );

        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::PARTIALLY_CAPTURED
            )
        );

        $this->assertFalse(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CAPTURED
            )
        );
    }

    /**
     * Assert that cancelling a payment works as intended.
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testCancel(): void
    {
        $response = $this->initFull();
        $validated = $this->validateCheckout(
            id: $response->id,
            version: $response->version
        );

        MockSignerRco::approveRco(
            checkout: $validated,
            ssn: $_ENV['RCO_JWT_GOVERNMENT_ID']
        );

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

        // Confirm events are tracked by payment history.
        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CANCEL_REQUESTED
            )
        );

        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::CANCELED
            )
        );

        $this->assertFalse(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::PARTIALLY_CANCELLED
            )
        );
    }

    /**
     * Assert that full refund refunds the full amount.
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testRefund(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );

        MockSignerRco::approveRco(
            checkout: $validated,
            ssn: $_ENV['RCO_JWT_GOVERNMENT_ID']
        );

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

        $this->assertEquals(
            expected: $captured->payment->status->capturedAmount,
            actual: $refunded->payment->status->refundedAmount
        );

        // Confirm events are tracked by payment history.
        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $refunded->id,
                event: Event::REFUND_REQUESTED
            )
        );

        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $refunded->id,
                event: Event::REFUNDED
            )
        );

        $this->assertFalse(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $refunded->id,
                event: Event::PARTIALLY_REFUNDED
            )
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
     * @throws Throwable
     * @throws ValidationException
     */
    public function testPartialRefund(): void
    {
        $checkout = $this->initFull();
        $validated = $this->validateCheckout(
            id: $checkout->id,
            version: $checkout->version
        );

        MockSignerRco::approveRco(
            checkout: $validated,
            ssn: $_ENV['RCO_JWT_GOVERNMENT_ID']
        );

        $fetched = Repository::get(id: $validated->id);

        $captured = Repository::capture(
            id: $fetched->id,
            version: $fetched->version
        );

        /** @var Cart\Item $cartItem */
        $cartItem = $captured->cart->items->toArray()[0];
        $transactionLines = new CreateTransactionLineCollection(data: [
            new CreateTransactionLine(
                type: $cartItem->type,
                description: $cartItem->description,
                itemId: $cartItem->itemId,
                itemIdDisplay: $cartItem->itemIdDisplay,
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

        $this->assertEquals(
            expected: $cartItem->totalPrice,
            actual: $result->payment->status->refundedAmount
        );

        // Confirm events are tracked by payment history.
        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::REFUND_REQUESTED
            )
        );

        $this->assertTrue(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::PARTIALLY_REFUNDED
            )
        );

        $this->assertFalse(
            condition: Config::getPaymentHistoryDataHandler()->hasExecuted(
                paymentId: $result->id,
                event: Event::REFUNDED
            )
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

        $newCustomer = new UpdateCustomer(
            type: $fetched->customer->type,
            governmentId: 'SE8305147715',
            billing: new CreateRecipient(
                name: $fetched->customer->billing->name,
                contact: new CreateContact(
                    firstName: $fetched->customer->billing->contact->firstName,
                    lastName: $fetched->customer->billing->contact->lastName,
                    email: $fetched->customer->billing->contact->email,
                    phone: $fetched->customer->billing->contact->phone
                ),
                address: new CreateAddress(
                    street: $fetched->customer->billing->address->street,
                    addressLine: $fetched->customer->billing->address->addressLine,
                    postalCode: $fetched->customer->billing->address->postalCode,
                    city: $fetched->customer->billing->address->city,
                    notes: $fetched->customer->billing->address->notes,
                    countryCode: $fetched->customer->billing->address->countryCode
                )
            ),
            delivery: new CreateRecipient(
                name: $fetched->customer->delivery->name,
                contact: new CreateContact(
                    firstName: $fetched->customer->delivery->contact->firstName,
                    lastName: $fetched->customer->delivery->contact->lastName,
                    email: $fetched->customer->delivery->contact->email,
                    phone: $fetched->customer->delivery->contact->phone
                ),
                address: new CreateAddress(
                    street: $fetched->customer->delivery->address->street,
                    addressLine: $fetched->customer->delivery->address->addressLine,
                    postalCode: $fetched->customer->delivery->address->postalCode,
                    city: $fetched->customer->delivery->address->city,
                    notes: $fetched->customer->delivery->address->notes,
                    countryCode: $fetched->customer->delivery->address->countryCode
                )
            )
        );

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
            Repository::getWebhookRequestData(post: $faultyData);
            $this->fail(message: 'Invalid webhook data accepted.');
        } catch (WebhookException) {
            $this->addToAssertionCount(count: 1);
        }

        // Simulate a complete CheckoutDto object in $_POST
        $full = json_encode(
            value: $this->initFull(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData(post: $full);
        $this->addToAssertionCount(count: 1);

        // Simulate a minimal CheckoutDto object in $_POST
        $mini = json_encode(
            value: $this->initMini(),
            flags: JSON_THROW_ON_ERROR
        );
        Repository::getWebhookRequestData(post: $mini);
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
