<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Api;

use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;

use function strlen;

/**
 * Tests for the Resursbank\Ecom\Lib\Api\Mapi class.
 */
class MapiTest extends TestCase
{
    /**
     * @var Mapi
     */
    private Mapi $mapi;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->mapi = new Mapi();

        $this->setupConfig();

        parent::setUp();
    }

    /**
     * @param bool $prod
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function setupConfig(
        bool $prod = false
    ): void {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            isProduction: $prod
        );
    }

    /**
     * Resolve a random route name.
     */
    private function getRoute(): string
    {
        $route = '';

        try {
            $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $length = random_int(min: 1, max: 50);

            for ($i = 0; $i < $length; $i++) {
                $route .= $charset[random_int(
                    min: 0,
                    max: strlen(string: $charset) - 1
                )];
            }
        } catch (Exception) {
            self::fail(message: 'Failed to generate route.');
        }

        return $route;
    }

    /**
     * @param string $route
     * @param string $host
     * @return string
     */
    private function getExpectedUrl(
        string $route = '',
        string $host = Mapi::URL_TEST
    ): string {
        return "$host$route";
    }

    /**
     * Assert getUrl() throws EmptyValueException without $route value.
     *
     * @return void
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlThrowsWithEmptyRoute(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->mapi->getUrl(route: '');
    }

    /**
     * Assert getUrl() returns URL to test endpoint.
     *
     * @return void
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlReturnsTestUrl(): void
    {
        $route = $this->getRoute();

        self::assertSame(
            expected: $this->getExpectedUrl(route: $route),
            actual: $this->mapi->getUrl(route: $route)
        );
    }

    /**
     * Assert getUrl() returns URL to production endpoint.
     *
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     * @throws ConfigException
     */
    public function testGetUrlReturnsProdUrl(): void
    {
        $this->setupConfig(prod: true);

        $route = $this->getRoute();

        self::assertSame(
            expected: $this->getExpectedUrl(
                route: $route,
                host: Mapi::URL_PROD
            ),
            actual: $this->mapi->getUrl(route: $route)
        );
    }
}
