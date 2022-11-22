<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Callback;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CallbackTypeException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Callback\Enum\CallbackType;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Callback\Repository;

/**
 * Testing received callbacks.
 */
class receiveCallbackTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(originalClassName: LoggerInterface::class),
            cache: $this->createMock(originalClassName: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: $_ENV['JWT_AUTH_SCOPE'],
                grantType: $_ENV['JWT_AUTH_GRANT_TYPE']
            )
        );
    }

    /**
     * @return void
     * @throws CallbackTypeException
     * @throws HttpException
     */
    public function testAuthorizationModel(): void
    {
        $authenticationCallbackRequest = $this->createMock(originalClassName: Repository::class);
        $authenticationCallbackReturn = new Authorization(
            paymentId: '123456789',
            status: Status::AUTHORIZED,
            created: '2021-09-01T12:00:00.000Z'
        );

        $authenticationCallbackRequest->method('getCallbackModel')->willReturn(
            $authenticationCallbackReturn
        );

        $this->assertSame(
            $authenticationCallbackRequest->getCallbackModel()::class,
            actual: Authorization::class
        );
    }

    /**
     * @return void
     * @throws CallbackTypeException
     * @throws HttpException
     */
    public function testManagementModel(): void
    {
        $authenticationCallbackRequest = $this->createMock(originalClassName: Repository::class);
        $authenticationCallbackReturn = new Management(
            paymentId: '123456789',
            Action: Action::CAPTURE,
            created: '2021-09-01T12:00:00.000Z',
            actionId: '123456789'
        );

        $authenticationCallbackRequest->method('getCallbackModel')->willReturn(
            $authenticationCallbackReturn
        );

        $this->assertSame(
            $authenticationCallbackRequest->getCallbackModel()::class,
            actual: Management::class
        );
    }

    /**
     * When php://input is not set.
     * @return void
     */
    public function testEmptyJsonModel(): void
    {
        $this->expectException(HttpException::class);
        new Repository(callbackType: CallbackType::AUTHORIZATION);
    }
}
