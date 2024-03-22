<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Payment\Widget;

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
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
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
use Resursbank\EcomTest\Utilities\MockSigner;

/**
 * Tests for the payment information widget.
 */
class PaymentInformationTest extends TestCase
{
    private Payment $payment;

    private PaymentInformation $widget;

    private string $orderReference;

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
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::SV
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
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     */
    private function createPayment(string $orderReference): Payment
    {
        $payment = Repository::create(
            storeId: $_ENV['STORE_ID'],
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
                governmentId: '198305147715',
                mobilePhone: '46701234567',
                deviceInfo: new DeviceInfo()
            )
        );

        MockSigner::approve(payment: $payment);

        return Repository::get(paymentId: $payment->id);
    }

    /**
     * Verify that widget renders
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testRenderWidget(): void
    {
        $this->assertEquals(
            expected: $this->payment->id,
            actual: $this->widget->payment->id,
            message: 'Widget payment id does not match original payment id'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>{$this->payment->id}<\/td>/s",
            string: $this->widget->content,
            message: 'Widget does not contain payment id cell.'
        );
    }

    /**
     * Verify that getTdEl() returns a td element with the given content.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testGetTdEl(): void
    {
        $tdEl = $this->widget->getTdEl(content: $this->payment->id);
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>{$this->payment->id}<\/td>/s",
            string: $tdEl,
            message: 'getTdEl() does not return a td element with the given content.'
        );

        // Verify any content I supply is returned in the td element.
        $content = 'test content';
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>{$content}<\/td>/s",
            string: $this->widget->getTdEl(content: $content),
            message: 'getTdEl() does not return a td element with the given content.'
        );

        // Verify that if $isHeader is true, renders header element.
        $headerEl = $this->widget->getTdEl(
            content: 'captured-amount',
            isHeader: true
        );

        // Assert style attribute contains font-weight:bold to confirm styling.
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*font-weight:bold.*>.*<\/td>/s",
            string: $headerEl,
            message: 'getTdEl() does not return a td element with the given content.'
        );

        // Assert that the content of the header element is translated.
        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>.*" . Translator::translate(
                phraseId: 'captured-amount'
            ) . ".*<\/td>/s",
            string: $headerEl,
            message: 'getTdEl() does not return a td element with the given content.'
        );
    }

    /**
     * Verify that getTrEl() returns a tr element with the given title and content.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testGetTrEl(): void
    {
        $content = 'some value';
        $trEl = $this->widget->getTrEl(
            title: 'captured-amount',
            content: $content
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<tr[^>]+style=.*>.*<\/tr>/s",
            string: $trEl,
            message: 'getTrEl() does not return a tr element.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>.*" . Translator::translate(
                phraseId: 'captured-amount'
            ) . ".*<\/td>/s",
            string: $trEl,
            message: 'getTrEl() does not return a td element header.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<td[^>]+style=.*>.*{$content}.*<\/td>/s",
            string: $trEl,
            message: 'getTrEl() does not return a td element with the given content.'
        );
    }

    /**
     * Verify that getTrStyle() toggles return value depending on state of
     * $eventTr property, which is toggled when method is called.
     */
    public function testGetTrStyle(): void
    {
        $this->widget->eventTr = true;

        $this->assertSame(
            expected: 'background-color: #006464;',
            actual: $this->widget->getTrStyle(),
            message: 'getTrStyle() does not return the expected value.'
        );

        $this->assertSame(
            expected: 'background-color: #009b96;',
            actual: $this->widget->getTrStyle(),
            message: 'getTrStyle() does not return the expected value.'
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
    public function testLogoRendering(): void
    {
        // Assert we render logo by default.
        $this->assertMatchesRegularExpression(
            pattern: "/<span[^>]+class=.*rb-pi-logo.*>.*<\/span>/s",
            string: $this->widget->content,
            message: 'Logo is not rendered by default.'
        );

        // Assert SVG element is present as well.
        $this->assertMatchesRegularExpression(
            pattern: "/<svg[^>]+xmlns=.*>.*<\/svg>/s",
            string: $this->widget->content,
            message: 'SVG element is not rendered.'
        );

        // Assert we do not render logo when renderLogo is false.
        $widget = new PaymentInformation(
            paymentId: $this->payment->id,
            currencySymbol: 'kr',
            currencyFormat: CurrencyFormat::SYMBOL_LAST,
            renderLogo: false
        );

        $this->assertDoesNotMatchRegularExpression(
            pattern: "/<span[^>]+class=.*rb-pi-logo.*>.*<\/span>/s",
            string: $widget->content,
            message: 'Logo is rendered when renderLogo is false.'
        );
    }
}
