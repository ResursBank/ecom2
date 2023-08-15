<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\UrlValidationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Rco\Repository;
use Resursbank\Ecom\Module\Rco\Widget\Checkout as CheckoutWidget;
use Resursbank\EcomTest\Utilities\Rco;

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
     * Assert that basic rendering of the widget works.
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
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testRenderWidget(): void
    {
        $checkout = Repository::init(request: Rco::getFullCheckout());

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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testRenderHead(): void
    {
        $checkout = Repository::init(request: Rco::getFullCheckout());

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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testRenderWidgetWithOptions(): void
    {
        $checkout = Repository::init(request: Rco::getFullCheckout());

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
     * @throws IllegalCharsetException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws UrlValidationException
     * @throws ValidationException
     */
    public function testRenderHeadWithStyling(): void
    {
        $checkout = Repository::init(request: Rco::getFullCheckout());

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
