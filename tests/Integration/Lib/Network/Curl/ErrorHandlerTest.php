<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network\Curl;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl\ErrorHandler;

/**
 * This class will test the curl error handler.
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * Assert validate() throws IllegalTypeException when body isn't string.
     *
     * @return void
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws AuthException
     */
    public function testValidateThrowsWithoutJsonContent(): void
    {
        $this->expectException(exception: IllegalTypeException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: true,
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws EmptyValueException when body is empty.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithEmptyJsonContent(): void
    {
        $this->expectException(exception: EmptyValueException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: '',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws JsonException when body isn't valid JSON.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithInvalidJsonContent(): void
    {
        $this->expectException(exception: JsonException::class);

        $handler = new ErrorHandler(
            ch: curl_init(),
            body: 'This is not JSON',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }

    /**
     * Assert validate() throws CurlException when body includes a message
     * property.
     *
     * Assert body property on CurlException is set.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithJsonMessage(): void
    {
        $this->expectException(exception: CurlException::class);

        try {
            $handler = new ErrorHandler(
                ch: curl_init(),
                body: '{"message": "This is a message"}',
                contentType: ContentType::JSON
            );


            $handler->validate();
        } catch (CurlException $e) {
            self::assertSame(
                expected: '{"message": "This is a message"}',
                actual: $e->body,
                message: 'Body mismatch.'
            );

            throw $e;
        }
    }

    /**
     * Assert validate() throws CurlException when HTTP response code is 404.
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     */
    public function testValidateThrowsWithHttpCode404(): void
    {
        $this->expectException(exception: CurlException::class);

        $ch = curl_init(
            url: Mapi::URL_TEST . '/404'
        );
        curl_exec(handle: $ch);

        $handler = new ErrorHandler(
            ch: $ch,
            body: '{}',
            contentType: ContentType::JSON
        );

        $handler->validate();
    }
}
