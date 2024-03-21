<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Rco\Repository;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\WebhookException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\PaymentHistory\DataHandler\VoidDataHandler;
use Resursbank\Ecom\Module\Rco\Repository\Webhook;

/**
 * Tests of Rco\Repository\Webhook behavior.
 */
class WebhookTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        Config::setup(
            logger: new NoneLogger(),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['RCO_JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['RCO_JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['RCO_JWT_AUTH_SCOPE']),
                grantType: GrantType::from(
                    value: $_ENV['RCO_JWT_AUTH_GRANT_TYPE']
                )
            ),
            paymentHistoryDataHandler: new VoidDataHandler()
        );
    }

    /**
     * Verify that an exception is thrown when no data passed to getRequestData.
     *
     * @throws WebhookException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function testNoData(): void
    {
        $this->expectException(exception: WebhookException::class);

        Webhook::getRequestData(post: null);
    }

    /**
     * Verify an exception is thrown when getRequestData receives broken data.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws WebhookException
     */
    public function testMalformedData(): void
    {
        $this->expectException(exception: WebhookException::class);

        Webhook::getRequestData(post: '{"foo:" "bar"}');
    }

    /**
     * Verify an exception is thrown when data to getRequestData is incomplete.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws WebhookException
     */
    public function testIncompleteData(): void
    {
        $this->expectException(exception: WebhookException::class);

        $data = '{
  "id": "6aa29192-056d-48a5-ac18-a0c009c3e3b3",
  "storeId": "23b213f8-0ea4-4c25-97b6-8810760087a8",
  "orderReference": "20240320rco9408",
  "countryCode": "SE",
  "locale": "sv-SE"
  }';
        Webhook::getRequestData(post: $data);
    }
}
