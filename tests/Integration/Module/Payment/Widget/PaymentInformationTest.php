<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Widget;

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
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\EcomTest\Unit\Lib\Model\PaymentTest;
use Resursbank\EcomTest\Utilities\MockSigner;
use Throwable;

/**
 * Tests for the payment information widget.
 */
class PaymentInformationTest extends TestCase
{
    private Payment $payment;

    private PaymentInformation $widget;

    /** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
    private string $orderReference;

    /**
     * Temporarily stored payment to test failures.
     */
    private Payment $paymentCache;

    /**
     * @throws EmptyValueException
     */
    protected function setUpEnglish(): void
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
    }

    /**
     * @throws ValidationException
     * @throws CurlException
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws IllegalTypeException
     * @throws AuthException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ConfigException
     * @throws ApiException
     * @throws ReflectionException
     * @throws FilesystemException
     */
    protected function setUp(): void
    {
        parent::setUp();

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
            language: Language::SV,
            storeId: $_ENV['STORE_ID']
        );

        $this->orderReference = Strings::generateRandomString(length: 12);
        $this->payment = $this->createPayment(
            orderReference: $this->orderReference
        );
        $this->widget = new PaymentInformation(
            paymentId: $this->payment->id,
            currencySymbol: 'kr',
            currencyFormat: CurrencyFormat::SYMBOL_LAST
        );
    }

    /**
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function createPayment(string $orderReference, string $governmentId = '198305147715'): Payment
    {
        $payment = Repository::create(
            paymentMethodId: $_ENV['PAYMENT_METHOD_ID'],
            orderLines: new OrderLineCollection(data: [
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Android',
                    reference: 'T-800',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
                new OrderLine(
                    quantity: 2.00,
                    quantityUnit: 'st',
                    vatRate: 25.00,
                    totalAmountIncludingVat: 301.5,
                    description: 'Robot',
                    reference: 'T-1000',
                    type: OrderLineType::PHYSICAL_GOODS,
                    unitAmountIncludingVat: 150.75,
                    totalVatAmount: 60.3
                ),
            ]),
            orderReference: $orderReference,
            customer: new Customer(
                deliveryAddress: new Address(
                    addressRow1: 'Glassgatan 15',
                    postalArea: 'Göteborg',
                    postalCode: '41655',
                    countryCode: CountryCode::SE
                ),
                customerType: CustomerType::NATURAL,
                contactPerson: 'Vincent',
                email: 'test@hosted.resurs.com',
                governmentId: $governmentId,
                mobilePhone: '0701234567',
                deviceInfo: new DeviceInfo()
            )
        );

        $this->paymentCache = $payment;

        MockSigner::approve(payment: $payment);

        return Repository::get(paymentId: $payment->id);
    }

    /**
     * Verify that widget renders
     */
    public function testRenderWidget(): void
    {
        $this->assertEquals(
            expected: $this->payment->id,
            actual: $this->widget->payment->id,
            message: 'Widget payment id does not match original payment id'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$this->payment->id}<\/td>/s",
            string: $this->widget->content,
            message: 'Widget does not contain payment id cell.'
        );
    }

    /**
     * Verify that getTdElement() returns a td element with the given content.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testGetTdElement(): void
    {
        $tdEl = $this->widget->getTdElement(content: $this->payment->id);
        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$this->payment->id}<\/td>/s",
            string: $tdEl,
            message: 'getTdElement() does not return a td element with the given content.'
        );

        // Verify any content I supply is returned in the td element.
        $content = 'test content';
        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$content}<\/td>/s",
            string: $this->widget->getTdElement(content: $content),
            message: 'getTdElement() does not return a td element with the given content.'
        );

        // Verify that if $isHeader is true, renders header element.
        $headerEl = $this->widget->getTdElement(
            content: 'captured-amount',
            isHeader: true
        );

        // Assert that the content of the header element is translated.
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+>.*" . Translator::translate(
                phraseId: 'captured-amount'
            ) . ".*<\/td>/s",
            string: $headerEl,
            message: 'getTdElement() does not return a td element with the given content.'
        );
    }

    /**
     * Verify that getTrElement() returns a tr element with the given title and content.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testGetTrElement(): void
    {
        $content = 'some value';
        $trEl = $this->widget->getTrElement(
            title: 'captured-amount',
            content: $content
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<tr>.*<\/tr>/s",
            string: $trEl,
            message: 'getTrElement() does not return a tr element.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+>.*" . Translator::translate(
                phraseId: 'captured-amount'
            ) . ".*<\/td>/s",
            string: $trEl,
            message: 'getTrElement() does not return a td element header.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+>.*{$content}.*<\/td>/s",
            string: $trEl,
            message: 'getTrElement() does not return a td element with the given content.'
        );
    }

    /**
     * Assert that getAddressContent() returns a string with all address data.
     */
    public function testGetAddressContent(): void
    {
        $addressContent = $this->widget->getAddressContent();
        $this->assertMatchesRegularExpression(
            pattern: "/<br \/>/",
            string: $addressContent,
            message: 'getAddressContent() does not return a string with <br /> separator.'
        );

        $data = [
            $this->widget->getAddressRow1(),
            $this->widget->getCity(),
            $this->widget->getPostalCode(),
            $this->widget->getCountryCode(),
        ];

        // Assert all values in $data are present in $addressContent.
        foreach ($data as $value) {
            $this->assertMatchesRegularExpression(
                pattern: "/{$value}/",
                string: $addressContent,
                message: 'getAddressContent() does not return a string with all address data.'
            );
        }
    }

    /**
     * Assert that the logo is rendered correctly depending on the value of
     * renderLogo.
     */
    public function testLogoRendering(): void
    {
        $this->assertMatchesRegularExpression(
            pattern: "/<svg[^>]+xmlns=.*>.*<\/svg>/s",
            string: $this->widget->content,
            message: 'SVG element is not rendered.'
        );
    }

    /**
     * Assert getCss() method works, and that the css property is assigned when
     * the widget is instantiated.
     */
    public function testCss(): void
    {
        // Assert that the css property on the widget instance is not empty.
        $this->assertNotEmpty($this->widget->css);

        // Assert that the static getCss() method returns a string.
        $this->assertIsString(PaymentInformation::getCss());

        // Assert that the static getCss() method returns the same value as the
        // css property on the widget instance.
        $this->assertEquals(
            expected: $this->widget->css,
            actual: PaymentInformation::getCss(),
            message: 'getCss() does not return the same value as the css property.'
        );

        // Assert that the css property contains CSS rules.
        $this->assertMatchesRegularExpression(
            pattern: "/\w+:\w+;/",
            string: $this->widget->css,
            message: 'CSS property does not contain CSS rules.'
        );
    }

    /**
     * Verify that realtime credit denial works. For tests related to the rejectedReasons model, see PaymentTest.
     *
     * @throws Throwable
     * @see PaymentTest
     */
    public function testCreditDenied(): void
    {
        $this->setUpEnglish();
        $orderReference = Strings::generateRandomString(length: 12);

        try {
            $this->createPayment(
                orderReference: $orderReference,
                governmentId: '195012026430'
            );
            $this->fail(message: 'Payment should have been rejected.');
        } catch (Throwable $e) {
            $this->assertStringContainsString('REJECTED', $e->getMessage());
        }

        $widget = new PaymentInformation(
            paymentId: $this->paymentCache->id,
            currencySymbol: 'kr',
            currencyFormat: CurrencyFormat::SYMBOL_LAST
        );

        $this->assertMatchesRegularExpression(
            pattern: '/<td(.*?)>Status<\/td><td>REJECTED \(Credit denied\)<\/td>/s',
            string: $widget->content,
            message: 'Payment was not rejected with CREDIT_DENIED.'
        );
    }
}
