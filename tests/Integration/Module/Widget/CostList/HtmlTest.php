<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\CostList;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Model\PriceSignage\CostCollection;
use Resursbank\Ecom\Lib\Model\PriceSignage\PriceSignage;
use Resursbank\Ecom\Lib\Model\PriceSignage\UriLinkCollection;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\Widget\CostList\Html;
use Throwable;

/**
 * Tests for the CostList HTML widget.
 */
class HtmlTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::EN,
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    /**
     * Generate a widget object.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    private function getWidget(): Html
    {
        $costCollection = new CostCollection(
            data: [
                new Cost(
                    name: Strings::generateRandomString(length: 12),
                    interest: 1.0,
                    durationMonths: 3,
                    setupFee: 12.0,
                    totalCost: 24.0,
                    monthlyCost: 36.0,
                    administrationFee: 48.0,
                    effectiveInterest: 72.0
                )
            ]
        );
        $priceSignage = new PriceSignage(
            secciLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            generalTermsLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            costList: $costCollection
        );
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );
        return new Html(priceSignage: $priceSignage, method: $paymentMethod);
    }

    /**
     * Verify that shouldRender behaves properly.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws Throwable
     */
    public function testShouldRender(): void
    {
        $costCollection = new CostCollection(
            data: []
        );
        $priceSignage = new PriceSignage(
            secciLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            generalTermsLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            costList: $costCollection
        );

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        $widget = new Html(priceSignage: $priceSignage, method: $paymentMethod);

        // count equals 0 && type !== invoice
        $this->assertFalse(
            condition: $widget->shouldRender()
        );

        // count equals 0 && type === invoice
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['INVOICE_PAYMENT_METHOD_ID']
        );
        $widget = new Html(priceSignage: $priceSignage, method: $paymentMethod);

        $this->assertFalse(
            condition: $widget->shouldRender()
        );

        // count greater than 0 && type === invoice
        $cost = new Cost(
            name: Strings::generateRandomString(length: 12),
            interest: 1.0,
            durationMonths: 3,
            setupFee: 0.0,
            totalCost: 0.0,
            monthlyCost: 0.0,
            administrationFee: 0.0,
            effectiveInterest: 0.0
        );
        $costCollection = new CostCollection(
            data: [
                $cost
            ]
        );
        $priceSignage = new PriceSignage(
            secciLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            generalTermsLinks: $this->createMock(
                originalClassName: UriLinkCollection::class
            ),
            costList: $costCollection
        );
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['INVOICE_PAYMENT_METHOD_ID']
        );
        $widget = new Html(priceSignage: $priceSignage, method: $paymentMethod);

        $this->assertFalse(
            condition: $widget->shouldRender()
        );

        // count greater than 0 && type !== invoice
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        $widget = new Html(priceSignage: $priceSignage, method: $paymentMethod);

        $this->assertTrue(
            condition: $widget->shouldRender()
        );
    }

    /**
     * Test rendering of setup fee.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testGetSetupFee(): void
    {
        $cost = new Cost(
            name: Strings::generateRandomString(length: 12),
            interest: 1.0,
            durationMonths: 3,
            setupFee: 12.0,
            totalCost: 24.0,
            monthlyCost: 36.0,
            administrationFee: 48.0,
            effectiveInterest: 72.0
        );
        $widget = $this->getWidget();

        $this->assertEquals(
            expected: 'Setup fee ' . Price::format(value: $cost->setupFee),
            actual: $widget->getSetupFee(cost: $cost)
        );
    }

    /**
     * Test rendering of administration fee.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testGetAdministrationFee(): void
    {
        $cost = new Cost(
            name: Strings::generateRandomString(length: 12),
            interest: 1.0,
            durationMonths: 3,
            setupFee: 12.0,
            totalCost: 24.0,
            monthlyCost: 36.0,
            administrationFee: 48.0,
            effectiveInterest: 72.0
        );
        $widget = $this->getWidget();

        $this->assertEquals(
            expected: 'Administration fee per month ' .
            Price::format(value: $cost->administrationFee),
            actual: $widget->getAdministrationFee(cost: $cost)
        );
    }

    /**
     * Test rendering of duration.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testGetDuration(): void
    {
        $cost = new Cost(
            name: Strings::generateRandomString(length: 12),
            interest: 1.0,
            durationMonths: 3,
            setupFee: 12.0,
            totalCost: 24.0,
            monthlyCost: 36.0,
            administrationFee: 48.0,
            effectiveInterest: 72.0
        );
        $widget = $this->getWidget();

        $this->assertEquals(
            expected: str_replace(
                search: '%1',
                replace: (string) $cost->durationMonths,
                subject: Translator::translate(
                    phraseId: 'part-payment-duration'
                )
            ),
            actual: $widget->getDuration(cost: $cost)
        );
    }

    /**
     * Verify that rendered widget contains expected data.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testRenderedContent(): void
    {
        $widget = $this->getWidget();

        $this->assertNotEmpty(actual: $widget->content);

        $this->assertStringContainsString(
            needle: '<div class="rb-ps-cl expanded" data-payment-method="' .
            $widget->method->id . '">',
            haystack: $widget->content
        );

        /** @var Cost $cost */
        foreach ($widget->priceSignage->costList as $cost) {
            $this->assertStringContainsString(
                needle: '<span>' . $widget->getDuration(cost: $cost) .
                '</span>',
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: '<span>' . $widget->getSetupFee(cost: $cost) .
                '</span>',
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: '<span>' . $widget->getAdministrationFee(cost: $cost) .
                '</span>',
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: Price::format(value: $cost->monthlyCost),
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: (string)$cost->interest,
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: (string)$cost->effectiveInterest,
                haystack: $widget->content
            );
            $this->assertStringContainsString(
                needle: Price::format(value: $cost->totalCost),
                haystack: $widget->content
            );
        }
    }
}
