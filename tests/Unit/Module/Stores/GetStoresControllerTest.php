<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Stores;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Store\Http\GetStoresController;

/**
 * Unit tests for the GetStoresController class.
 */
class GetStoresControllerTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Config::setup();
        parent::setUp();
    }

    /**
     * Get controller object with mocked getInputData output.
     */
    private function getControllerWithMockedInputData(
        string $data
    ): GetStoresController {
        $controller = $this->getMockBuilder(GetStoresController::class)
            ->onlyMethods(['getInputData'])
            ->getMock();

        $controller->expects($this->once())
            ->method(constraint: 'getInputData')
            ->willReturn(value: $data);

        return $controller;
    }

    /**
     * Test output of a valid request.
     *
     * @throws ConfigException
     */
    public function testValidRequest(): void
    {
        $controller = $this->getControllerWithMockedInputData(
            data: '{"environment": "test", "clientId": "clientId", "clientSecret": "clientSecret"}'
        );

        try {
            $result = $controller->getRequestData();

            $this->assertEquals(
                expected: Environment::TEST,
                actual: $result->environment
            );
            $this->assertEquals(
                expected: 'clientId',
                actual: $result->clientId
            );
            $this->assertEquals(
                expected: 'clientSecret',
                actual: $result->clientSecret
            );

            $this->addToAssertionCount(count: 1);
        } catch (HttpException) {
            $this->fail();
        }
    }

    /**
     * Verify that getRequestData throws HttpException for invalid data.
     *
     * @throws ConfigException
     * @throws HttpException
     */
    public function testResultNotGetStoresRequest(): void
    {
        $controller = $this->getMockBuilder(GetStoresController::class)
            ->onlyMethods(['getRequestModel'])
            ->getMock();

        $controller->expects($this->once())
            ->method(constraint: 'getRequestModel')
            ->willReturn(value: new Model());

        $this->expectException(HttpException::class);
        $controller->getRequestData();
    }
}
