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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Network\Model\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\ReadMore;

/**
 * Integration tests for PaymentMethods repository.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
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
     */
    public function testRenderReadMore(): void
    {
        ob_start();
        new ReadMore(
            paymentMethod: $this->method,
            amount: $this->method->maxPurchaseLimit,
            label: 'This is a link'
        );

        $content = ob_get_clean();

        self::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm/s',
            string: $content,
            message: 'Read more widget should contain a div with class rb-rm.'
        );

        self::assertStringContainsString(
            needle: 'This is a link',
            haystack: $content,
            message: 'Read more widget should contain the label.'
        );

        self::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-rm-link/s',
            string: $content,
            message: 'Read more widget should contain a div with class rb-rm-link.'
        );

        $testUrl = str_replace(
            search: ['/', '?', '&', '-', '.'],
            replace: ['\\/', '\\?', '\\&', '\\-', '\\.'],
            subject: $this->url
        );

        self::assertMatchesRegularExpression(
            pattern: "/<iframe[^>]+src=[\"']$testUrl/s",
            string: $content,
            message: 'Read more widget should contain an iframe with the correct URL.'
        );

        self::assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"']/s",
            string: $content,
            message: 'Read more widget should contain a div with the correct ID.'
        );

        self::assertMatchesRegularExpression(
            pattern: "/<div[^>]+id=[\"']rb-rm-model-{$this->method->id}[\"'][^>]+style=[\"'][^\"']*display:\s*none;/s",
            string: $content,
            message: 'Read more widgets lightbox should be hidden by default.'
        );
    }

    /**
     * @return void
     * @throws FilesystemException
     */
    public function testRenderReadMoreDefaultLabel(): void
    {
        if ($_ENV['is_pipeline']) {
            self::markTestSkipped(message: 'Running in pipeline, skipping test.');
        }

        ob_start();
        new ReadMore(
            paymentMethod: $this->method,
            amount: $this->method->maxPurchaseLimit
        );

        $content = ob_get_clean();

        self::assertStringContainsString(
            needle: 'Read more',
            haystack: $content,
            message: 'Read more widget should contain a link with text "Read more".'
        );
    }
}
