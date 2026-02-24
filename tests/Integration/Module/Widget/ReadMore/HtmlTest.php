<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\ReadMore;

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
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink\Type;
use Resursbank\Ecom\Lib\Model\PriceSignage\Language as PriceSignageLanguage;
use Resursbank\Ecom\Lib\Model\PriceSignage\UriLink;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PriceSignage\Repository as PriceSignageRepository;
use Resursbank\Ecom\Module\Widget\ReadMore\Html;
use Throwable;

/**
 * Integration tests for the ReadMore widget.
 */
class HtmlTest extends TestCase
{
    private PaymentMethod $paymentMethod;

    private string $url;

    /**
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
     * @throws ValidationException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/readMore/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        $this->paymentMethod = $paymentMethod;

        $links = PriceSignageRepository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->paymentMethod->maxPurchaseLimit
        );

        /** @var UriLink $secciLink */
        foreach ($links->secciLinks as $secciLink) {
            if (
                !$this->isConfigLanguage(secciLanguage: $secciLink->language)
            ) {
                continue;
            }

            $this->url = $secciLink->uri;
        }

        parent::setUp();
    }

    private function isConfigLanguage(
        PriceSignageLanguage $secciLanguage
    ): bool {
        $comparisonTable = [
            'Swedish' => 'sv',
            'Norwegian' => 'no',
            'Finnish' => 'fi',
            'Danish' => 'da'
        ];

        return
            array_key_exists(
                key: $secciLanguage->value,
                array: $comparisonTable
            )
            && $comparisonTable[$secciLanguage->value]
                === Config::getLanguage()->value
            ;
    }

    /**
     * Verify basic behavior of getSecciUrl method.
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
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     * @throws Throwable
     */
    public function testGetSecciUrl(): void
    {
        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit
        );

        $links = PriceSignageRepository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->paymentMethod->maxPurchaseLimit
        );

        $link = '';

        /** @var UriLink $secciLink */
        foreach ($links->secciLinks as $secciLink) {
            if ($this->isConfigLanguage(secciLanguage: $secciLink->language)) {
                $link = $secciLink->uri;
                break;
            }
        }

        $this->assertNotEmpty(
            actual: $widget->getSecciUrl()
        );

        $this->assertEquals(
            expected: $link,
            actual: $widget->getSecciUrl()
        );
    }

    /**
     * Verify basic behavior of getLegacyUrl method.
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
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testGetLegacyUrl(): void
    {
        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit
        );

        $link = '';

        /** @var LegalLink $legalLink */
        foreach ($this->paymentMethod->legalLinks as $legalLink) {
            if ($legalLink->type !== Type::PRICE_INFO) {
                continue;
            }

            $link = $legalLink->url . $this->paymentMethod->maxPurchaseLimit;
            break;
        }

        $this->assertNotEmpty(
            actual: $widget->getLegacyUrl()
        );

        $this->assertEquals(
            expected: $link,
            actual: $widget->getLegacyUrl()
        );
    }

    /**
     * Verify that the useLegacyLink flag works as intended.
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
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testUseLegacyLink(): void
    {
        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit,
            useLegacyLink: true
        );

        $this->assertEquals(
            expected: $widget->getLegacyUrl(),
            actual: $widget->url
        );

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit,
            useLegacyLink: false
        );

        $this->assertEquals(
            expected: $widget->getSecciUrl(),
            actual: $widget->url
        );
    }

    /**
     * Verify that if no SECCI link should exist we get an empty string.
     *
     * For this we use the English language setting in the config as there
     * should be no SECCI link in English.
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
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public function testEnglishLanguage(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/readMore/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::EN,
            storeId: $_ENV['STORE_ID']
        );

        $widget = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit,
            useLegacyLink: false
        );

        $this->assertEmpty(
            actual: $widget->getSecciUrl()
        );
    }

    /**
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testRenderReadMore(): void
    {
        if ((bool) $_ENV['IS_PIPELINE']) {
            $this->markTestSkipped(
                message: 'Buffer does not work in pipeline, skipping.'
            );
        }

        $data = new Html(
            paymentMethod: $this->paymentMethod,
            amount: $this->paymentMethod->maxPurchaseLimit
        );

        $this->assertStringContainsString(
            needle: Translator::translate(phraseId: 'read-more'),
            haystack: $data->content,
            message: 'Read more link not found.'
        );

        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm/s',
            string: $data->content,
            message: 'Read more widget should contain a div with class rb-rm.'
        );

        $this->assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm-link/s',
            string: $data->content,
            message: 'Read more widget should contain a div with class rb-rm-link.'
        );

        $testUrl = str_replace(
            search: ['/', '?', '&', '-', '.'],
            replace: ['\\/', '\\?', '\\&', '\\-', '\\.'],
            subject: $this->url
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<iframe[^>]+src=[\"']$testUrl/s",
            string: $data->content,
            message: 'Read more widget should contain an iframe with the correct URL.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->paymentMethod->id}[\"']/s",
            string: $data->content,
            message: 'Read more widget should contain a div with the correct ID.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-" .
            $this->paymentMethod->id . "[\"'][^>]+style=[\"'][^\"']*display:\s*none;/s",
            string: $data->content,
            message: 'Read more widget lightbox should be hidden by default.'
        );
    }
}
