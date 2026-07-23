<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Repository\Traits;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use stdClass;

/**
 * Tests for the DataResolver class.
 */
class DataResolverTest extends TestCase
{
    use DataResolver;

    /**
     * Verify non-stdClass data parameter triggers exception.
     *
     * @throws ApiException
     */
    public function testNonStdClassData(): void
    {
        $this->expectException(ApiException::class);
        $this->resolveResponseData(data: 'foo');
    }

    /**
     * Verify that exception is thrown when trying to extract non-property.
     *
     * @throws ApiException
     */
    public function testMissingProperty(): void
    {
        $data = new stdClass();
        $data->foo = 'bar';

        $this->expectException(ApiException::class);
        $this->resolveResponseData(data: $data, extractProperty: 'baz');
    }

    /**
     * Verify that exception thrown if property data type not compatible.
     *
     * @throws ApiException
     */
    public function testIncompatibleDataType(): void
    {
        $data = new stdClass();
        $data->foo = 1234;

        $this->expectException(ApiException::class);
        $this->resolveResponseData(data: $data, extractProperty: 'foo');
    }
}
