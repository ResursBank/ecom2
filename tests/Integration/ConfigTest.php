<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;

/**
 * Integration tests for Config class.
 */
class ConfigTest extends TestCase
{
    /**
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        self::connect(storeId: $_ENV['STORE_ID']);
        parent::setUp();
    }

    /**
     * Establish API connection.
     *
     * @throws EmptyValueException
     */
    private function connect(
        ?string $storeId = null,
        ?Language $language = null
    ): void {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/stores/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $storeId,
            language: $language
        );
    }

    /**
     * Assert language is accurately returned to us, based on store configured.
     *
     * NOTE: This test assumes a none-english API account. This should be fine
     * since there are no English API accounts, it's only a fallback language.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     */
    public function testGetLanguage(): void
    {
        // Connect without store id, assert EN is returned.
        self::connect();
        $this->assertSame(
            expected: Language::EN,
            actual: Config::getLanguage()
        );

        // Connect with store id from $_ENV, assert EN is not returned.
        self::connect(storeId: $_ENV['STORE_ID']);
        $this->assertNotSame(
            expected: Language::EN,
            actual: Config::getLanguage()
        );

        // Confirm that configured language is returned (check using two
        // different languages to avoid false positives when using a API account
        // with the same language as one of the two).
        self::connect(storeId: $_ENV['STORE_ID'], language: Language::FI);

        $this->assertSame(
            expected: Language::FI,
            actual: Config::getLanguage()
        );

        self::connect(storeId: $_ENV['STORE_ID'], language: Language::DA);

        $this->assertSame(
            expected: Language::DA,
            actual: Config::getLanguage()
        );
    }
}
