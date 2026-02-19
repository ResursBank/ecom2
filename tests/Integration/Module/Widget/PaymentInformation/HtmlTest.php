<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentInformation;

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
use Resursbank\Ecom\Exception\TimeoutException;
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
use Resursbank\Ecom\Lib\Utilities\MockSigner;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\Widget\PaymentInformation\Html;
use Resursbank\EcomTest\Unit\Lib\Model\PaymentTest;
use Throwable;

/**
 * Tests for the payment information widget.
 */
class HtmlTest extends TestCase
{
    private string $orderReference;

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
     * @throws EmptyValueException
     * @throws Exception
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
    private function createPayment(
        string $orderReference,
        string $governmentId = '198305147715'
    ): Payment {
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
                email: 'test@hosted.resurs.com',
                governmentId: $governmentId,
                mobilePhone: '0701234567',
                deviceInfo: new DeviceInfo()
            ),
            metadata: MockSigner::getMetadata()
        );

        try {
            MockSigner::callCustomerUrl(payment: $payment);
        } catch (TimeoutException $error) {
            if ($_ENV['IS_PIPELINE']) {
                $this->markTestSkipped(
                    message: 'MockSigner failed with timeout.'
                );
            }

            throw $error;
        }

        return Repository::get(paymentId: $payment->id);
    }

    /**
     * Verify that widget renders
     */
    public function testRenderWidget(): void
    {
        $payment = $this->createPayment(orderReference: $this->orderReference);
        $widget = new Html(paymentId: $payment->id);
        $this->assertEquals(
            expected: $payment->id,
            actual: $widget->payment->id,
            message: 'Widget payment id does not match original payment id'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$payment->id}<\/td>/s",
            string: $widget->content,
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
        $payment = $this->createPayment(orderReference: $this->orderReference);
        $widget = new Html(paymentId: $payment->id);
        $tdEl = $widget->getTdElement(content: $payment->id);
        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$payment->id}<\/td>/s",
            string: $tdEl,
            message: 'getTdElement() does not return a td element with the ' .
            'given content.'
        );

        // Verify any content I supply is returned in the td element.
        $content = 'test content';
        $this->assertMatchesRegularExpression(
            pattern: "/<td>{$content}<\/td>/s",
            string: $widget->getTdElement(content: $content),
            message: 'getTdElement() does not return a td element with the ' .
            'given content.'
        );

        // Verify that if $isHeader is true, renders header element.
        $headerEl = $widget->getTdElement(
            content: 'captured-amount',
            isHeader: true
        );

        // Assert that the content of the header element is translated.
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+>.*" . Translator::translate(
                phraseId: 'captured-amount'
            ) . ".*<\/td>/s",
            string: $headerEl,
            message: 'getTdElement() does not return a td element with the ' .
            'given content.'
        );
    }

    /**
     * Verify that getTrElement() returns a correct tr element.
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
        $payment = $this->createPayment(orderReference: $this->orderReference);
        $widget = new Html(paymentId: $payment->id);
        $content = 'some value';
        $trEl = $widget->getTrElement(
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
            message: 'getTrElement() does not return a td element with the ' .
            'given content.'
        );
    }

    /**
     * Assert that getAddressContent() returns a string with all address data.
     */
    public function testGetAddressContent(): void
    {
        $payment = $this->createPayment(orderReference: $this->orderReference);
        $widget = new Html(paymentId: $payment->id);
        $addressContent = $widget->getAddressContent();
        $this->assertMatchesRegularExpression(
            pattern: "/<br \/>/",
            string: $addressContent,
            message: 'getAddressContent() does not return a string with ' .
            '<br /> separator.'
        );

        $data = [
            $widget->getAddressRow1(),
            $widget->getCity(),
            $widget->getPostalCode(),
            $widget->getCountryCode(),
        ];

        // Assert all values in $data are present in $addressContent.
        foreach ($data as $value) {
            $this->assertMatchesRegularExpression(
                pattern: "/{$value}/",
                string: $addressContent,
                message: 'getAddressContent() does not return a string with ' .
                'all address data.'
            );
        }
    }

    /**
     * Assert that the logo is rendered correctly.
     */
    public function testLogoRendering(): void
    {
        $payment = $this->createPayment(orderReference: $this->orderReference);
        $widget = new Html(paymentId: $payment->id);
        $this->assertMatchesRegularExpression(
            pattern: "/<svg[^>]+xmlns=.*>.*<\/svg>/s",
            string: $widget->content,
            message: 'SVG element is not rendered.'
        );
    }

    /**
     * Verify that realtime credit denial works.
     *
     * @throws Throwable
     * @see PaymentTest
     */
    public function testCreditDenied(): void
    {
        $this->setUpEnglish();
        $payment = $this->createPayment(
            orderReference: $this->orderReference,
            governmentId: '197211072793'
        );

        // Sleep for a few seconds to make sure status has been updated.
        sleep(seconds: 3);

        $widget = new Html(paymentId: $payment->id);

        $this->assertMatchesRegularExpression(
            pattern: '/<td(.*?)>Status<\/td><td>REJECTED \(Credit denied\)<\/td>/s',
            string: $widget->content,
            message: 'Payment was not rejected with CREDIT_DENIED.'
        );
    }
}
