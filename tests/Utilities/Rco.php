<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Utilities;

use Exception;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Rco\Address;
use Resursbank\Ecom\Lib\Model\Rco\Callback;
use Resursbank\Ecom\Lib\Model\Rco\Callbacks;
use Resursbank\Ecom\Lib\Model\Rco\Contact;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\Item;
use Resursbank\Ecom\Lib\Model\Rco\CreateCart\ItemCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckbox;
use Resursbank\Ecom\Lib\Model\Rco\CreateCheckout\CreateCheckboxCollection;
use Resursbank\Ecom\Lib\Model\Rco\CreateOptions;
use Resursbank\Ecom\Lib\Model\Rco\Customer;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CartItemType;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Merchant;
use Resursbank\Ecom\Lib\Model\Rco\Recipient;
use Resursbank\Ecom\Lib\Model\Rco\Redirects;
use Resursbank\Ecom\Lib\Model\Rco\Webhook;
use Resursbank\Ecom\Lib\Model\Rco\Webhooks;
use Resursbank\Ecom\Lib\Utilities\Strings;

/**
 * RCO helpers.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
class Rco
{
    /**
     * Fetch items for cart creation.
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    private static function getItemCollection(): ItemCollection
    {
        return new ItemCollection(
            data: [
                new Item(
                    type: CartItemType::PRODUCT,
                    itemId: 'item01',
                    description: 'An Item',
                    quantityUnit: 'st',
                    unitPrice: 1000,
                    quantity: 1,
                    taxRate: 25,
                    totalDiscount: 0,
                    url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '',
                    imageUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/image.jpg',
                    mutable: true
                ),
                new Item(
                    type: CartItemType::PRODUCT,
                    itemId: 'item02',
                    description: 'An Item',
                    quantityUnit: 'st',
                    unitPrice: 2000,
                    quantity: 1,
                    taxRate: 25,
                    totalDiscount: 0,
                    url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '',
                    imageUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/image.jpg',
                    mutable: true
                )
            ]
        );
    }

    /**
     * Resolve the smallest possible object to initiate checkout session from.
     *
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public static function getMiniCheckout(): CreateCheckout
    {
        return new CreateCheckout(
            cart: new CreateCart(
                code: '',
                items: new ItemCollection(
                    data: [
                        new Item(
                            type: CartItemType::PRODUCT,
                            itemId: 'item01',
                            description: 'An Item',
                            quantityUnit: 'st',
                            quantity: 1,
                            unitPrice: 1000,
                            taxRate: 25
                        )
                    ]
                )
            ),
            merchant: new Merchant(
                displayName: 'Resurs Stuff AB',
                termsUrl: 'https://example.com'
            )
        );
    }

    /**
     * Resolve complete object structure to initiate checkout from.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Exception
     */
    public static function getFullCheckout(
        ?string $orderReference = null
    ): CreateCheckout {
        if ($orderReference === null) {
            $orderReference = Strings::generateRandomString(length: 12);
        }

        $jwt = Config::getJwtAuth();

        if ($jwt === null) {
            throw new ConfigException(message: 'Missing JWT auth token!');
        }

        $auth = 'Bearer ' . $jwt->getToken();
        return new CreateCheckout(
            cart: new CreateCart(
                items: self::getItemCollection(),
                code: ''
            ),
            merchant: new Merchant(
                displayName: 'Resurs Stuff AB',
                termsUrl: 'https://example.com',
                logoUrl: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/logoUrl.jpg',
                homepageUrl: $_ENV['RCOPLUS_HOMEPAGE_URL']
            ),
            orderReference: $orderReference,
            options: new CreateOptions(),
            customer: new Customer(
                type: Type::B2C,
                governmentId: 'SE8305147715',
                billing: new Recipient(
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
                delivery: new Recipient(
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
            redirects: new Redirects(
                checkout: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/checkout',
                success: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/success'
            ),
            callbacks: self::getCallbacks(),
            webhooks: self::getWebhooks(auth: $auth),
            locale: Locale::sv_SE,
            currency: Currency::SEK,
            checkboxes: new CreateCheckboxCollection(data: [
                new CreateCheckbox(
                    id: 'terms',
                    label: 'Terms and conditions',
                    checked: true,
                    required: true
                )
            ])
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public static function getCart(): CreateCart
    {
        return new CreateCart(
            items: new ItemCollection(
                data: [
                    new Item(
                        type: CartItemType::PRODUCT,
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

    /**
     * Get Callbacks property.
     */
    public static function getCallbacks(): Callbacks
    {
        return new Callbacks(
            authorization: new Callback(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/authorized'
            ),
            management: new Callback(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/management'
            )
        );
    }

    /**
     * Fetch web hooks.
     */
    public static function getWebhooks(string $auth): Webhooks
    {
        return new Webhooks(
            customer: new Webhook(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/customer',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            cart: new Webhook(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/cart',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            shipping: new Webhook(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/shipping',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            payment: new Webhook(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/payment',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            ),
            validate: new Webhook(
                url: $_ENV['RCOPLUS_HOMEPAGE_URL'] . '/webhooks/validate',
                authorization: $auth,
                continueOnNoResponse: true,
                timeout: 60
            )
        );
    }
}
