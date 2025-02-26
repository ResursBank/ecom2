<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Network;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Lib\Model\Network\Response;
use stdClass;

use function is_string;

/**
 * This class will test curl methods.
 */
class CurlTest extends TestCase
{
    private function getRequestBodyObject(
        Response $response
    ): stdClass {
        if (!($response->body instanceof stdClass)) {
            $this->fail(message: 'Response body is not an object.');
        }

        return $response->body;
    }

    private function validateRequestMethod(
        Response $response,
        string $expected
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->REQUEST_METHOD)) {
            $this->fail(message: 'No REQUEST_METHOD found in response body.');
        }

        $this->assertSame(expected: $expected, actual: $body->REQUEST_METHOD);
    }

    private function validateUserAgent(
        Response $response,
        string $startsWith
    ): void {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->HTTP_USER_AGENT)) {
            $this->fail(message: 'No HTTP_USER_AGENT found in response body.');
        }

        if (!is_string(value: $body->HTTP_USER_AGENT)) {
            $this->fail(
                message: 'HTTP_USER_AGENT in response body is not a string.'
            );
        }

        $this->assertTrue(
            condition: str_starts_with(
                haystack: $body->HTTP_USER_AGENT,
                needle: $startsWith
            )
        );
    }

    private function getInput(
        Response $response
    ): string {
        $body = $this->getRequestBodyObject(response: $response);

        if (!isset($body->input)) {
            $this->fail(message: 'No input found in response body.');
        }

        if (!is_string(value: $body->input)) {
            $this->fail(message: 'input in response body is not a string.');
        }

        return $body->input;
    }
}
