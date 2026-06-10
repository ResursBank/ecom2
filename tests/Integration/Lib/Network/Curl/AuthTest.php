<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Curl;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Network\Curl\Auth;

/**
 * Tests for Auth.
 */
class AuthTest extends TestCase
{
    /**
     * Verify that setJwtAuth throws error if there's no JWT auth set.
     *
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public function testSetJwtAuthThrowsOnNoAuth(): void
    {
        Config::setup();
        $this->expectException(ConfigException::class);
        Auth::setJwtAuth(curl_init());
    }
}
