<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Callback;

use DateTime;
use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\CreditApplication;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Result;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Status;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\FileDataHandler;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\Callback\Repository;
use Resursbank\Ecom\Module\PaymentHistory\Repository
    as PaymentHistoryRepository;

/**
 * Tests for the Callback\Repository class.
 */
class RepositoryTest extends TestCase
{
    public string $dataHandlerLocation;

    public string $logDirectory;

    /**
     * @throws AttributeCombinationException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws FormatException
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->dataHandlerLocation = '/tmp/resursbank/callbacktest';

        if (!file_exists($this->dataHandlerLocation)) {
            mkdir(directory: $this->dataHandlerLocation, recursive: true);
        }

        $this->dataHandlerLocation .= '/' .
            Strings::generateRandomString(length: 12);

        $this->logDirectory = '/tmp/resursbank/callbacktest/logs';

        if (!file_exists($this->logDirectory)) {
            mkdir(directory: $this->logDirectory, recursive: true);
        }

        Config::setup(
            logger: new FileLogger(
                path: $this->logDirectory
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            paymentHistoryDataHandler: new FileDataHandler(
                file: $this->dataHandlerLocation
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unlink(filename: $this->logDirectory . '/ecom.log');
        unlink(filename: $this->dataHandlerLocation);
    }

    /**
     * Test URL validation in triggerTest.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     */
    public function testTriggerTestUrlValidation(): void
    {
        $this->expectException(exception: IllegalValueException::class);

        Repository::triggerTest(url: 'httx:///foo_');
    }

    /**
     * Verify behavior of the process method.
     *
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function testProcess(): void
    {
        $paymentId = Strings::getUuid();

        $result = Repository::process(
            callback: new Authorization(
                paymentId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            ),
            process: static fn (): Result => Result::SUCCESS
        );

        $historyList = PaymentHistoryRepository::getList(paymentId: $paymentId);

        $this->assertIsIterable(actual: $historyList);
        $this->assertCount(expectedCount: 2, haystack: $historyList);
        $this->assertNotNull(actual: $historyList[0]);

        if (!$historyList[0] instanceof Entry) {
            $this->fail(message: 'List item not of type Entry');
        }

        $this->assertEquals(
            expected: Status::AUTHORIZED->value,
            actual: $historyList[0]->extra
        );
        $this->assertEquals(expected: 202, actual: $result);

        $result = Repository::process(
            callback: new Authorization(
                paymentId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            ),
            process: static function (): void {
                throw new Exception();
            }
        );

        $this->assertEquals(expected: 408, actual: $result);

        $result = Repository::process(
            callback: new Authorization(
                paymentId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            ),
            process: static function (): void {
                throw new HttpException(code: 500);
            }
        );

        $this->assertEquals(expected: 500, actual: $result);

        $history = PaymentHistoryRepository::getList(
            paymentId: $paymentId,
            event: Event::CALLBACK_FAILED
        );

        $this->assertNotNull(actual: $history);
        $this->assertCount(expectedCount: 2, haystack: $history);
    }

    /**
     * Verify basic behavior of trackInit.
     *
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws EmptyValueException
     */
    public function testTrackInit(): void
    {
        // authorization
        $paymentId = Strings::getUuid();
        Repository::trackInit(
            paymentId: $paymentId,
            callback: new Authorization(
                paymentId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $historyList = PaymentHistoryRepository::getList(paymentId: $paymentId);

        $this->assertNotNull(actual: $historyList);
        $this->assertCount(expectedCount: 1, haystack: $historyList);

        if (!$historyList[0] instanceof Entry) {
            $this->fail(message: 'List item not of type Entry.');
        }

        $this->assertEquals(
            expected: Status::AUTHORIZED->value,
            actual: $historyList[0]->extra
        );

        // creditapplication
        $paymentId = Strings::getUuid();
        Repository::trackInit(
            paymentId: $paymentId,
            callback: new CreditApplication(
                applicationId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $historyList = PaymentHistoryRepository::getList(paymentId: $paymentId);

        $this->assertNotNull(actual: $historyList);
        $this->assertCount(expectedCount: 1, haystack: $historyList);
        $this->assertNotNull(actual: $historyList[0]);

        if (!$historyList[0] instanceof Entry) {
            $this->fail(message: 'List item not of type Entry.');
        }

        $this->assertEquals(
            expected: Status::AUTHORIZED->value,
            actual: $historyList[0]->extra
        );

        // management
        $paymentId = Strings::getUuid();
        Repository::trackInit(
            paymentId: $paymentId,
            callback: new Management(
                paymentId: $paymentId,
                action: Action::CAPTURE,
                actionId: Strings::getUuid(),
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $historyList = PaymentHistoryRepository::getList(paymentId: $paymentId);

        $this->assertNotNull(actual: $historyList);
        $this->assertCount(expectedCount: 1, haystack: $historyList);
        $this->assertNotNull(actual: $historyList[0]);

        if (!$historyList[0] instanceof Entry) {
            $this->fail(message: 'List item not of type Entry.');
        }

        $this->assertNull(actual: $historyList[0]->extra);
    }

    /**
     * Verify behavior of addDebugLogs.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testAddDebugLogs(): void
    {
        // management
        $paymentId = Strings::getUuid();
        $actionId = Strings::getUuid();
        Repository::addDebugLogs(
            callback: new Management(
                paymentId: $paymentId,
                action: Action::CAPTURE,
                actionId: $actionId,
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $logFile = file(
            filename: $this->logDirectory . '/ecom.log',
            flags: FILE_SKIP_EMPTY_LINES
        );
        $this->assertIsArray(actual: $logFile);
        $this->assertArrayHasKey(key: 0, array: $logFile);
        $this->assertStringContainsString(
            needle: 'DEBUG: Processing management callback for ' . $paymentId .
            ', action ' . Action::CAPTURE->value . ' (' . $actionId . ')',
            haystack: $logFile[0]
        );

        // authorization
        unlink(filename: $this->logDirectory . '/ecom.log');
        $paymentId = Strings::getUuid();
        Repository::addDebugLogs(
            callback: new Authorization(
                paymentId: $paymentId,
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $logFile = file(
            filename: $this->logDirectory . '/ecom.log',
            flags: FILE_SKIP_EMPTY_LINES
        );
        $this->assertIsArray(actual: $logFile);
        $this->assertArrayHasKey(key: 0, array: $logFile);
        $this->assertStringContainsString(
            needle: 'DEBUG: Processing authorization callback for ' .
            $paymentId . ', status ' . Status::AUTHORIZED->value,
            haystack: $logFile[0]
        );

        // creditapplication
        unlink(filename: $this->logDirectory . '/ecom.log');
        Repository::addDebugLogs(
            callback: new CreditApplication(
                applicationId: Strings::getUuid(),
                status: Status::AUTHORIZED,
                created: (new DateTime())->format('Y-m-d H:i:s')
            )
        );

        $logFile = file(
            filename: $this->logDirectory . '/ecom.log',
            flags: FILE_SKIP_EMPTY_LINES
        );
        $this->assertEmpty(actual: $logFile);
    }
}
