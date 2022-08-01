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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use stdClass;

use function is_string;
use function strlen;

/**
 * Assert the None cache driver works as expected.
 *
 * @psalm-suppress PropertyNotSetInConstructor
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
     */
    private function setupConfig(
        bool $prod = false
    ): void {
        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
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
     * @param array $params
     * @return string
     */
    private function getExpectedUrl(
        string $route = '',
        string $host = Mapi::HOST_TEST,
        array $params = []
    ): string {
        $paramList = implode(separator: '/', array: array_map(
            static function (string $v, mixed $k): string {
                return is_string(value: $k) ? "$k/$v" : $v;
            },
            $params,
            array_keys(array: $params)
        ));

        return "https://$host/$route" . (count($params) ? "/$paramList" : '');
    }

    /**
     * Assert getUrl() throws EmptyValueException without $route value.
     *
     * @return void
     * @throws ValidationException
     */
    public function testGetUrlThrowsWithEmptyRoute(): void
    {
        $this->expectException(exception: EmptyValueException::class);
        $this->mapi->getUrl(route: '');
    }

    /**
     * Assert getUrl() throws IllegalCharsetException when $route includes
     * illegal characters.
     *
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlThrowsWithIllegalRoute(): void
    {
        $this->expectException(exception: IllegalCharsetException::class);
        $this->mapi->getUrl(route: 'test?');
    }

    /**
     * Assert getUrl() returns URL to test endpoint.
     *
     * @return void
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
     */
    public function testGetUrlReturnsProdUrl(): void
    {
        $this->setupConfig(prod: true);

        $route = $this->getRoute();

        self::assertSame(
            expected: $this->getExpectedUrl(
                route: $route,
                host: Mapi::HOST_PROD
            ),
            actual: $this->mapi->getUrl(route: $route)
        );
    }

    /**
     * Assert getUrl() accepts sequential route segments.
     *
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlAcceptsSequentialParams(): void
    {
        $params = ['param1', 'param2', 'param3'];
        $route = $this->getRoute();

        self::assertSame(
            expected: $this->getExpectedUrl(route: $route, params: $params),
            actual: $this->mapi->getUrl(route: $route, params: $params)
        );
    }

    /**
     * Assert getUrl() accepts associative route segments.
     *
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlAcceptsAssocParams(): void
    {
        $params = ['test' => 'param1', 'test2' => 'param2', 'bada' => 'param3'];
        $route = $this->getRoute();

        self::assertSame(
            expected: $this->getExpectedUrl(route: $route, params: $params),
            actual: $this->mapi->getUrl(route: $route, params: $params)
        );
    }

    /**
     * Assert getUrl() result in an error if $params include a non-string value.
     *
     * @return void
     * @throws EmptyValueException
     * @throws ValidationException
     */
    public function testGetUrlThrowsWithIllegalParamType(): void
    {
        $this->expectError();
        $this->mapi->getUrl(route: 'test', params: ['test' => new stdClass()]);
    }
}
