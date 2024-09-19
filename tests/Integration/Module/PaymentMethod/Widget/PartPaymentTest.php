<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentMethod\Widget;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\AnnuityFactor\Repository as AnnuityFactorRepository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment;
use Throwable;

/**
 * Integration test for the Part payment widget
 */
class PartPaymentTest extends TestCase
{
    private ?PaymentMethod $method;

    private PartPayment $widget;

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
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->method = Repository::getById(
            storeId: $_ENV['STORE_ID'],
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($this->method === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $this->widget = new PartPayment(
            storeId: $_ENV['STORE_ID'],
            paymentMethod: $this->method,
            months: 3,
            amount: 1200,
            currencyFormat: CurrencyFormat::SYMBOL_LAST,
            currencySymbol: 'kr',
            fetchStartingCostUrl: 'https://example.com'
        );
    }

    /**
     * Resolve the longest interest free duration from the annuity factors.
     *
     * @throws TestException
     */
    private function getLongestInterestFreeDuration(): int
    {
        try {
            $collection = AnnuityFactorRepository::getAnnuityFactors(
                storeId: $_ENV['STORE_ID'],
                paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
            );
        } catch (Throwable) {
            throw new TestException(message: 'Failed to load annuity factors');
        }

        $result = 0;

        foreach ($collection->data as $factor) {
            if ($factor->interest !== 0.0) {
                continue;
            }

            $result = max($result, $factor->durationMonths);
        }

        return $result;
    }

    /**
     * Verify that getStartingAt() returns a string matching expected format.
     */
    public function testGetStartingAt(): void
    {
        try {
            $this->assertMatchesRegularExpression(
                pattern: '/^Starting at [\d,.]+ .* per month \(.*\)$/',
                string: $this->widget->getStartingAt(),
                message: 'Starting at should be formatted correctly.'
            );
        } catch (Throwable) {
            $this->fail('Starting at should not throw an exception.');
        }

        // Mock return value of \Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment::isEligible
        // to return false, and check tha the string returned by getStartingAt()
        // is the same as the one returned by getNotEligibleMessage().
        $this->widget = $this->createPartialMock(
            PartPayment::class,
            ['isEligible']
        );

        $this->widget->method('isEligible')
            ->willReturn(false);

        $this->assertEquals(
            expected: $this->widget->getNotEligibleMessage(),
            actual: $this->widget->getStartingAt(),
            message: 'Starting at should be the same as not eligible message.'
        );
    }

    /**
     * Confirm widget HTML content is rendered as expected.
     */
    public function testWidgetContent(): void
    {
        // Confirm main element is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp.'
        );

        // Confirm SVG logo element is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/<svg.*>/',
            string: $this->widget->content,
            message: 'Widget should contain an SVG logo.'
        );

        // Confirm div with class rb-pp-info is rendered when displayInfoText
        // is true.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-info/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-info.'
        );

        if ($this->method === null) {
            $this->fail('Payment method failed to load');
        }

        try {
            $noInfoText = $this->widget = new PartPayment(
                storeId: $_ENV['STORE_ID'],
                paymentMethod: $this->method,
                months: 3,
                amount: 1200,
                currencyFormat: CurrencyFormat::SYMBOL_LAST,
                currencySymbol: 'kr',
                fetchStartingCostUrl: 'https://example.com',
                displayInfoText: false
            );
        } catch (Throwable) {
            $this->fail('Widget should not throw an exception.');
        }

        // Confirm div with class rb-pp-info is not rendered when displayInfoText
        // is false.
        $this->assertDoesNotMatchRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-info/',
            string: $noInfoText->content,
            message: 'Widget should not contain a div with class rb-pp-info.'
        );

        // Confirm div with class rb-pp-starting-at is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-starting-at/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-starting-at.'
        );

        // Confirm starting at cost is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/Starting at [\d,.]+ .* per month/',
            string: $this->widget->content,
            message: 'Widget should contain starting at cost.'
        );

        // Confirm there is a div with the class rb-pp-error.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-error/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-error.'
        );

        // Confirm there is a rb-pp-overlay element, and that it is hidden.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-overlay["\'][^>]*style=["\'][^"\']*display: none/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-overlay and style display: none.'
        );

        // Confirm there is a rb-pp-loader element, and that it is hidden.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-loader["\'][^>]*style=["\'][^"\']*display: none/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-loader and style display: none.'
        );

        // Confirm rb-pp-spinner element is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-pp-spinner/',
            string: $this->widget->content,
            message: 'Widget should contain a div with class rb-pp-spinner.'
        );
    }

    /**
     * Confirm widget CSS content is rendered as expected.
     */
    public function testWidgetCss(): void
    {
        // Confirm CSS content is rendered.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp/',
            string: $this->widget->css,
            message: 'Widget should contain CSS content.'
        );

        // Confirm CSS content contains rb-pp-info.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-info/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-info.'
        );

        // Confirm CSS content contains rb-pp-starting-at.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-starting-at/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-starting-at.'
        );

        // Confirm CSS content contains rb-pp-error.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-error/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-error.'
        );

        // Confirm CSS content contains rb-pp-overlay.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-overlay/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-overlay.'
        );

        // Confirm CSS content contains rb-pp-loader.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-loader/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-loader.'
        );

        // Confirm CSS content contains rb-pp-spinner.
        $this->assertMatchesRegularExpression(
            pattern: '/\.rb-pp-spinner/',
            string: $this->widget->css,
            message: 'Widget CSS should contain rb-pp-spinner.'
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
            string: $this->widget->js,
            message: 'Widget JS should define class Resursbank_PartPayment.'
        );
    }

    /**
     * Verify $logo property on widget instance is rendered and contains and SVG
     * element.
     */
    public function testWidgetLogo(): void
    {
        // Confirm logo is an SVG element.
        $this->assertMatchesRegularExpression(
            pattern: '/^<svg.*/',
            string: $this->widget->logo,
            message: 'Widget logo should be an SVG element.'
        );
    }

    /**
     * Verify the $cost property is assigned on the widget instance when its
     * created (make sure it's not null).
     */
    public function testWidgetCost(): void
    {
        // Confirm cost property is not null.
        $this->assertNotNull(
            $this->widget->cost,
            'Widget cost property should not be null.'
        );
    }

    /**
     * Confirm that getLongestPeriodWithZeroInterest() returns the expected value.
     *
     * @throws TestException
     * @throws ConfigException
     */
    public function testGetLongestPeriodWithZeroInterest(): void
    {
        $this->assertEquals(
            expected: $this->getLongestInterestFreeDuration(),
            actual: $this->widget->getLongestPeriodWithZeroInterest(),
            message: 'Longest period with zero interest should be 36 months.'
        );
    }

    /**
     * Confirm that getNotEligibleMessage() returns the expected value.
     *
     * @throws ConfigException
     * @throws TestException
     */
    public function testGetNotEligibleMessage(): void
    {
        $duration = $this->getLongestInterestFreeDuration();

        // Confirm that getNotEligibleMessage() contains $duration.
        $this->assertStringContainsString(
            needle: (string) $duration,
            haystack: $this->widget->getNotEligibleMessage(),
            message: 'Not eligible message should contain the longest interest free duration.'
        );

        // Mock return of \Resursbank\Ecom\Module\PaymentMethod\Widget\PartPayment::getLongestPeriodWithZeroInterest
        // to return 0, and check that the message is empty.
        $this->widget = $this->createPartialMock(
            PartPayment::class,
            ['getLongestPeriodWithZeroInterest']
        );

        $this->widget->method('getLongestPeriodWithZeroInterest')
            ->willReturn(0);

        $this->assertEmpty(
            $this->widget->getNotEligibleMessage(),
            'Not eligible message should be empty when longest interest free duration is 0.'
        );
    }
}
