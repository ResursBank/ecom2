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
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks\Callback;
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
use Resursbank\Ecom\Module\Rco\Widget\Checkout as CheckoutWidget;

/**
 * Tests for the RCO+ widget.
 */
class CheckoutTest extends TestCase
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
            orderReference: $this->generateOrderReference(length: 12),
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
            callbacks: new Callback(
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
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/payment',
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
     * Assert that basic rendering of the widget works.
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
     * @throws FilesystemException
     * @throws ValidationException
     */
    public function testRenderWidget(): void
    {
        $checkout = Repository::init(checkout: $this->getCheckout());

        if (!$checkout->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        $widget = new CheckoutWidget(checkoutId: $checkout->id);

        $body = $widget->getBodyElement();

        $this->assertStringContainsString(
            haystack: $body,
            needle: $checkout->id
        );
    }

    /**
     * Assert that basic rendering of the widget's header script works.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRenderHead(): void
    {
        $checkout = Repository::init(checkout: $this->getCheckout());

        if (!$checkout->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        $widget = new CheckoutWidget(checkoutId: $checkout->id);

        $head = $widget->getHeaderScript();

        $this->assertStringContainsString(
            haystack: $head,
            needle: $widget->getScriptUrl()
        );
    }

    /**
     * Assert that rendering the widget with options works.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRenderWidgetWithOptions(): void
    {
        $checkout = Repository::init(checkout: $this->getCheckout());

        if (!$checkout->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        $widget = new CheckoutWidget(
            checkoutId: $checkout->id,
            locale: Locale::NB,
            disabled: true,
            collapseCart: true
        );

        $body = $widget->getBodyElement();

        $this->assertStringContainsString(
            needle: 'collapseCart',
            haystack: $body
        );
        $this->assertStringContainsString(needle: 'disabled', haystack: $body);
        $this->assertStringContainsString(
            needle: 'locale="' . Locale::NB->value . '"',
            haystack: $body
        );
    }

    /**
     * Assert that rendering the header section with styling works.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRenderHeadWithStyling(): void
    {
        $checkout = Repository::init(checkout: $this->getCheckout());

        if (!$checkout->id) {
            throw new IllegalValueException(
                message: 'Property "id" missing from Init response.'
            );
        }

        $widget = new CheckoutWidget(
            checkoutId: $checkout->id,
            style: new CheckoutWidget\Style(
                primaryColor: '#ff0000',
                buttonRadius: '12px'
            )
        );

        $head = $widget->getHeaderScript();

        $this->assertStringContainsString(
            needle: '--rco-primary-color: #ff0000;',
            haystack: $head
        );
        $this->assertStringNotContainsString(
            needle: '--rco-button-bg-color:',
            haystack: $head
        );
    }
}
