<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Exception;

use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Enum\InvalidFieldName;

/**
 * Test CurlException functionality.
 */
class CurlExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        // Required by CurlException::getDetailedMessage().
        Config::setup();

        parent::setUp();
    }

    public function testGetDetailedMessageWithNoJsonBody(): void
    {
        $msg = 'Original message.';
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: 'not a json',
            httpCode: 400
        );
        $result = $exception->getDetailedMessage(msg: $msg);
        $this->assertSame(expected: $msg, actual: $result);
    }

    public function testGetDetailedMessageWithGovernmentIdField(): void
    {
        $msg = 'Original message.';
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.governmentId']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );
        $result = $exception->getDetailedMessage(msg: $msg);
        $this->assertStringStartsWith(prefix: $msg, string: $result);
        $this->assertGreaterThan(
            minimum: strlen(string: $msg),
            actual: strlen(string: $result)
        );
    }

    public function testGetDetailedMessageWithMobileField(): void
    {
        $msg = 'Original message.';
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.mobilePhone']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );
        $result = $exception->getDetailedMessage(msg: $msg);
        $this->assertStringStartsWith(prefix: $msg, string: $result);
        $this->assertGreaterThan(
            minimum: strlen(string: $msg),
            actual: strlen(string: $result)
        );
    }

    public function testGetDetailedMessageWithUnsupportedField(): void
    {
        $msg = 'Original message.';
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.unsupportedField']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );
        $result = $exception->getDetailedMessage(msg: $msg);
        $this->assertSame(expected: $msg, actual: $result);
    }

    public function testGetInvalidFieldNameThrowsJsonExceptionOnInvalidJson(): void
    {
        $this->expectException(exception: JsonException::class);

        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: 'not a valid json',
            httpCode: 400
        );

        $exception->getInvalidFieldName();
    }

    public function testGetInvalidFieldNameReturnsUnknownOnEmptyArray(): void
    {
        $body = json_encode(value: []);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(expected: InvalidFieldName::UNKNOWN, actual: $result);
    }

    public function testGetInvalidFieldNameReturnsUnknownOnUnexpectedStructure(): void
    {
        $body = json_encode(value: [
            'test' => [
                'testing' => 5
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(expected: InvalidFieldName::UNKNOWN, actual: $result);
    }

    public function testGetInvalidFieldNameReturnsGovernmentIdOnCorrectStructure(): void
    {
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.governmentId']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(
            expected: InvalidFieldName::GOVERNMENT_ID,
            actual: $result
        );
    }

    public function testGetInvalidFieldNameReturnsPhoneOnCorrectStructure(): void
    {
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.mobilePhone']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(expected: InvalidFieldName::PHONE, actual: $result);
    }

    public function testGetInvalidFieldNameReturnsEmailOnCorrectStructure(): void
    {
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.email']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(expected: InvalidFieldName::EMAIL, actual: $result);
    }

    public function testGetInvalidFieldNameReturnsUnknownOnUnrecognizedField(): void
    {
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.someUnknownField']
            ]
        ]);
        $exception = new CurlException(
            message: 'Curl error',
            code: 0,
            body: $body,
            httpCode: 400
        );

        $result = $exception->getInvalidFieldName();
        $this->assertSame(expected: InvalidFieldName::UNKNOWN, actual: $result);
    }
}
