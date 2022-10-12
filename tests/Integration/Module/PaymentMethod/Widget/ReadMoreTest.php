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
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;

/**
 * Integration tests for the ReadMore widget.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadMoreTest extends TestCase
{
    /**
     * @var PaymentMethod
     */
    private PaymentMethod $method;

    /**
     * @var string
     */
    private string $url;

    /**
     * @return void
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: new Filesystem(path: '/tmp/ecom-test/readMore/' . time()),
            jwtAuth: new Jwt(
                clientId: (string) $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: (string) $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: (string) $_ENV['JWT_AUTH_SCOPE'],
                grantType: (string) $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );

        $method = Repository::getById(
            storeId: (string) $_ENV['STORE_ID'],
            paymentMethodId: (string) $_ENV['ANNUITY_PAYMENT_METHOD_ID']
        );

        if ($method === null) {
            self::fail(message: 'No annuity payment method found.');
        }

        $this->method = $method;

        foreach ($this->method->legalLinks as $link) {
            if ($link->type === 'PRICE_INFO') {
                $this->url = $link->url;
            }
        }

        parent::setUp();
    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testRenderReadMore(): void
    {
        if ((bool) $_ENV['IS_PIPELINE']) {
            self::markTestSkipped(
                message: 'Buffer does not work in pipeline, skipping.'
            );
        }

        $data = new ReadMore(
            paymentMethod: $this->method,
            amount: $this->method->maxPurchaseLimit
        );

        self::assertStringContainsString(
            needle: Translator::translate(phraseId: 'read-more'),
            haystack: $data->content,
            message: 'Read more link not found.'
        );

        self::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm/s',
            string: $data->content,
            message: 'Read more widget should contain a div with class rb-rm.'
        );

        self::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm-link/s',
            string: $data->content,
            message: 'Read more widget should contain a div with class rb-rm-link.'
        );

        $testUrl = str_replace(
            search: ['/', '?', '&', '-', '.'],
            replace: ['\\/', '\\?', '\\&', '\\-', '\\.'],
            subject: $this->url
        );

        self::assertMatchesRegularExpression(
            pattern: "/<iframe[^>]+src=[\"']$testUrl/s",
            string: $data->content,
            message: 'Read more widget should contain an iframe with the correct URL.'
        );

        self::assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"']/s",
            string: $data->content,
            message: 'Read more widget should contain a div with the correct ID.'
        );

        self::assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"'][^>]+style=[\"'][^\"']*display:\s*none;/s",
            string: $data->content,
            message: 'Read more widget lightbox should be hidden by default.'
        );

        self::assertStringContainsString(
            needle: '.rb-rm-link p',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-link class'
        );
        self::assertStringContainsString(
            needle: '.rb-rm-background',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-background class'
        );
        self::assertStringContainsString(
            needle: '.rb-rm-iframe-container',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-iframe-container class'
        );
        self::assertStringContainsString(
            needle: '.rb-rm-iframe',
            haystack: $data->css,
            message: 'Read more widget CSS should contain section for the rb-rm-iframe class'
        );
    }
}
