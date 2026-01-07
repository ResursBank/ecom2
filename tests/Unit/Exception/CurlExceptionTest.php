<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;

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
                ['fieldName' => 'customer.governmentId.whatever']
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
            expected: strlen(string: $msg),
            actual: strlen(string: $result)
        );
    }

    public function testGetDetailedMessageWithMobileField(): void
    {
        $msg = 'Original message.';
        $body = json_encode(value: [
            'validationErrors' => [
                ['fieldName' => 'customer.mobilePhoneNumber']
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
            expected: strlen(string: $msg),
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
}
