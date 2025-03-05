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
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Language;
use Resursbank\Ecom\Lib\Model\PriceSignage\UriLink;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;
use Resursbank\Ecom\Module\PriceSignage\Repository as PriceSignageRepository;

/**
 * Integration tests for the ReadMore widget.
 */
class ReadMoreTest extends TestCase
{
    private PaymentMethod $method;

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

        $method = Repository::getById(
            paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($method === null) {
            $this->fail(message: 'No annuity payment method found.');
        }

        $this->method = $method;

        $links = PriceSignageRepository::getPriceSignage(
            paymentMethodId: $this->method->id,
            amount: $this->method->maxPurchaseLimit
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
        Language $secciLanguage
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

        $data = new ReadMore(
            paymentMethod: $this->method,
            amount: $this->method->maxPurchaseLimit
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
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"']/s",
            string: $data->content,
            message: 'Read more widget should contain a div with the correct ID.'
        );

        $this->assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"'][^>]+style=[\"'][^\"']*display:\s*none;/s",
            string: $data->content,
            message: 'Read more widget lightbox should be hidden by default.'
        );

        $this->assertStringContainsString(
            needle: '.rb-rm-link div',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-link class'
        );
        $this->assertStringContainsString(
            needle: '.rb-rm-background',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-background class'
        );
        $this->assertStringContainsString(
            needle: '.rb-rm-iframe-container',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-iframe-container class'
        );
        $this->assertStringContainsString(
            needle: '.rb-rm-iframe',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-iframe class'
        );
    }
}
