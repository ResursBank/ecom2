<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Api;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalCharsetException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use function is_string;

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

    private function getRoute(): string
    {
        $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = random_int(1, 50);
        

    }

    /**
     * @param array $params
     * @return string
     */
    private function getExpectedUrl(
        string $host = Mapi::HOST_TEST,
        array $params = []
    ): string {
        return implode(separator: '/', array: array_map(
            static function($v, $k): string {
                return is_string(value: $k) ? "$k/$v" : $v;
            },
            $params,
            array_keys(array: $params)
        ));
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
        $route = 'TesTing';

        self::assertSame(
            expected: 'https://' . Mapi::HOST_TEST . "/$route",
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

        $route = 'mighty';

        self::assertSame(
            expected: 'https://' . Mapi::HOST_PROD . "/$route",
            actual: $this->mapi->getUrl(route: $route)
        );
    }

    public function testGetUrlAcceptsSequentialParams(): void
    {
        $params = ['param1', 'param2', 'param3'];

        self::assertSame(
            exepected: ''
        );
    }
}
