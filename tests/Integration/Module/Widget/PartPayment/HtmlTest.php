<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PartPayment;

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
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\AnnuityFactor\Repository as AnnuityFactorRepository;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PriceSignage\Repository as PriceSignageRepository;
use Resursbank\Ecom\Module\Widget\PartPayment\Html;
use Throwable;

/**
 * Integration test for the Part payment widget
 */
class HtmlTest extends TestCase
{
    private ?PaymentMethod $paymentMethod;

    private Html $widget;

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

        $this->widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: 1200,
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
        $this->assertMatchesRegularExpression(
            pattern: '/^Pay [\d,.]+ kr\/month for ' . $this->widget->months .
                ' months \([0-9\.]+% interest rate\)\.$/',
            string: $this->widget->getStartingAt(),
            message: 'Starting at should be formatted correctly.'
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 12,
            amount: 5,
            fetchStartingCostUrl: 'https://example.com',
            threshold: 5000
        );

        $this->assertEquals(
            expected: $widget->getNotEligibleMessage(),
            actual: $widget->getStartingAt(),
            message: 'Starting at should be the same as not eligible message.'
        );
    }

    /**
     * Verify that the warning is present.
     */
    public function testWarning(): void
    {
        $this->assertStringContainsString(
            needle: $this->widget->warning->content,
            haystack: $this->widget->content
        );
    }

    /**
     * Verify that the Read More link is present in the widget HTML.
     */
    public function testReadMoreLinkPresent(): void
    {
        $this->assertStringContainsString(
            needle: $this->widget->readMore->url,
            haystack: $this->widget->content
        );
    }

    /**
     * Verify that the legacy link parameter works.
     *
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
    public function testUseLegacyReadMoreLink(): void
    {
        $legacyLink = '';
        $amount = 100;

        if ($this->paymentMethod === null) {
            $this->fail('Payment method failed to load');
        }

        /** @var LegalLink $link */
        foreach ($this->paymentMethod->legalLinks as $link) {
            if ($link->type !== Type::PRICE_INFO) {
                continue;
            }

            $legacyLink = $link->url . $amount;
            break;
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: $amount,
            fetchStartingCostUrl: 'https://example.com'
        );

        $this->assertNotEquals(
            expected: $legacyLink,
            actual: $widget->readMore->url
        );

        if ($this->paymentMethod === null) {
            $this->fail('Payment method failed to load');
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: $amount,
            fetchStartingCostUrl: 'https://example.com',
            useLegacyReadMoreLink: true
        );

        $this->assertEquals(
            expected: $legacyLink,
            actual: $widget->readMore->url
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

        if ($this->paymentMethod === null) {
            $this->fail('Payment method failed to load');
        }

        try {
            $noInfoText = $this->widget = new Html(
                paymentMethod: $this->paymentMethod,
                months: 3,
                amount: 1200,
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
            pattern: '/Pay [\d,.]+ .*\/month for 3 months/',
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
     * Verify $logo property on widget is rendered and contains an SVG element.
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
     * Verify the $cost property is set when widget is created.
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
            originalClassName: Html::class,
            methods: ['getLongestPeriodWithZeroInterest']
        );

        $this->widget->method('getLongestPeriodWithZeroInterest')
            ->willReturn(0);

        $this->assertEmpty(
            $this->widget->getNotEligibleMessage(),
            'Not eligible message should be empty when longest interest free duration is 0.'
        );
    }

    /**
     * Test getCost output.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function testGetCost(): void
    {
        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $result = $this->widget->getCost(
            paymentMethod: $this->paymentMethod,
            amount: $this->widget->amount,
            months: $this->widget->months
        );

        $fetched = PriceSignageRepository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->widget->amount,
            monthFilter: $this->widget->months
        );
        $filtered = array_values(array: $fetched->costList->toArray())[0];

        $this->assertEqualsCanonicalizing(expected: $filtered, actual: $result);
    }

    /**
     * Test the getTotalCost method output.
     *
     * @throws ConfigException
     */
    public function testGetTotalCost(): void
    {
        $this->assertMatchesRegularExpression(
            pattern: '/For \d+ months, the total cost will be [\d]+./',
            string: $this->widget->getTotalCost()
        );
    }

    /**
     * Test the getSetupFee method output.
     *
     * @throws ConfigException
     */
    public function testGetSetupFee(): void
    {
        $this->assertMatchesRegularExpression(
            pattern: '/Setup fee: \d+/',
            string: $this->widget->getSetupFee()
        );
    }

    /**
     * Test the getAdministrationFee method output.
     *
     * @throws ConfigException
     */
    public function testGetAdministrationFee(): void
    {
        $this->assertMatchesRegularExpression(
            pattern: '/Administration fee per month: \d+/',
            string: $this->widget->getAdministrationFee()
        );
    }

    /**
     * Test the output of the shouldDisplayCostExample.
     *
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
    public function testShouldDisplayCostExample(): void
    {
        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 12,
            amount: 100,
            fetchStartingCostUrl: 'http://example.com/'
        );

        $this->assertMatchesRegularExpression(
            pattern: '/Pay \d+,\d+ kr\/month for \d+ months/',
            string: $widget->content
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 12,
            amount: 100,
            fetchStartingCostUrl: 'http://example.com/',
            showCostExample: false
        );

        $this->assertDoesNotMatchRegularExpression(
            pattern: '/Pay \d+,\d+ kr\/month for \d+ months/',
            string: $widget->content
        );

        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['INVOICE_PAYMENT_METHOD_ID']
        );

        if ($paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new Html(
            paymentMethod: $paymentMethod,
            months: 12,
            amount: 100,
            fetchStartingCostUrl: 'http://example.com/'
        );

        $this->assertDoesNotMatchRegularExpression(
            pattern: '/Pay \d+,\d+ kr\/month for \d+ months/',
            string: $widget->content
        );

        if ($this->paymentMethod === null) {
            throw new EmptyValueException(
                message: 'Payment method failed to load'
            );
        }

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            months: 3,
            amount: 100,
            fetchStartingCostUrl: 'http://example.com/',
            threshold: 1000
        );

        $this->assertDoesNotMatchRegularExpression(
            pattern: '/Pay \d+,\d+ kr\/month for \d+ months/',
            string: $widget->content
        );
    }

    /**
     * Test getFormattedCost method.
     *
     * @throws ConfigException
     */
    public function testGetFormattedCost(): void
    {
        $cost = $this->widget->cost;
        $formattedCost = $this->widget->getFormattedCost($cost->totalCost);
        $priceFormatted = Price::format(value: $cost->totalCost);

        $this->assertEquals(expected: $priceFormatted, actual: $formattedCost);
    }
}
