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
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Widget\PaymentMethods;

/**
 * Integration tests for PaymentMethods repository.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodsTest extends TestCase
{
    /**
     * @var PaymentMethodCollection
     */
    private PaymentMethodCollection $methods;

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

        $this->methods = Repository::getPaymentMethods(
            storeId: (string) $_ENV['STORE_ID'],
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testRenderReadMore(): void
    {
        $data = new PaymentMethods(paymentMethods: $this->methods);

        self::assertStringContainsString(
            needle: Translator::translate('name'),
            haystack: $data->content,
            message: 'Name table header not found.'
        );

        self::assertStringContainsString(
            needle: Translator::translate('min-total'),
            haystack: $data->content,
            message: 'Minimum total table header not found.'
        );

        self::assertStringContainsString(
            needle: Translator::translate('max-total'),
            haystack: $data->content,
            message: 'Maximum total table header not found.'
        );

        self::assertStringContainsString(
            needle: Translator::translate('sort-order'),
            haystack: $data->content,
            message: 'Sort order table header not found.'
        );

        self::assertMatchesRegularExpression(
            pattern: '/<div[^>]+class=["\'][^"\']*rb-payment-methods/s',
            string: $data->content,
            message: 'Payment methods widget should contain a div with class rb-payment-methods.'
        );
    }
}
