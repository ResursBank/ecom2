<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PartPayment;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\Widget\PartPayment\Js;
use Throwable;

/**
 * Integration test for the Part payment JS widget.
 */
#[AllowMockObjectsWithoutExpectations]
class JsTest extends TestCase
{
    private Js $widget;

    private ?PaymentMethod $paymentMethod;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::EN,
            storeId: $_ENV['STORE_ID']
        );

        $this->paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $this->widget = new Js(
            amount: 1200,
            paymentMethod: $this->paymentMethod,
            months: 3,
            showCostExample: true
        );
    }

    /**
     * Confirm widget JavaScript content is rendered as expected.
     */
    public function testWidgetJs(): void
    {
        // Confirm class Resursbank_PartPayment is defined.
        $this->assertMatchesRegularExpression(
            pattern: '/class Resursbank_PartPayment/',
            string: $this->widget->content,
            message: 'Widget JS should define class Resursbank_PartPayment.'
        );

        $this->assertStringContainsString(
            needle: 'https://example.com',
            haystack: $this->widget->content
        );

        $this->assertStringContainsString(
            needle: 'eligibleCost = true',
            haystack: $this->widget->content
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $this->widget = new Js(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: 1200,
            fetchStartingCostUrl: 'https://example.com',
            showCostExample: false
        );

        $this->assertStringContainsString(
            needle: 'eligibleCost = false',
            haystack: $this->widget->content
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $this->widget = new Js(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: 1200,
            fetchStartingCostUrl: 'https://example.com',
            threshold: 0
        );

        $this->assertStringNotContainsString(
            needle: 'if (parseFloat(data.startingAt)',
            haystack: $this->widget->content
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $this->widget = new Js(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: 1200,
            fetchStartingCostUrl: 'https://example.com',
            threshold: 100
        );

        $this->assertStringContainsString(
            needle: 'if (parseFloat(data.startingAt)',
            haystack: $this->widget->content
        );
    }
}
