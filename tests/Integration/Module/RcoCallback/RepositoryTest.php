<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\RcoCallback;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Basic;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\DigestConfiguration;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\Request;
use Resursbank\Ecom\Module\RcoCallback\Repository;

/**
 * Tests for RCO callback module Repository class.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws EmptyValueException
     * @throws JsonException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setUp(): void
    {
        // Set up Config object
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(
                username: (string)$_ENV['BASIC_AUTH_USERNAME'],
                password: (string)$_ENV['BASIC_AUTH_PASSWORD']
            )
        );

        // Clear existing callbacks
        $eventNames = ['TEST', 'UNFREEZE', 'BOOKED', 'UPDATE'];
        foreach ($eventNames as $eventName) {
            Repository::deleteCallback(eventName: $eventName);
        }

        parent::setUp();
    }

    /**
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ValidationException
     */
    protected function tearDown(): void
    {
        // Clear existing callbacks
        $eventNames = ['TEST', 'UNFREEZE', 'BOOKED', 'UPDATE'];
        foreach ($eventNames as $eventName) {
            Repository::deleteCallback(eventName: $eventName);
        }

        parent::tearDown();
    }

    /**
     * Verify that we can register, fetch and delete callbacks
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function testRegisterGetAndDeleteCallback(): void
    {
        if (Config::$instance->basicAuth === null) {
            self::fail(message: 'Basic auth is not configured.');
        }

        $eventName = 'BOOKED';
        $request = new Request(
            uriTemplate: 'https://example.com/dummy?id={paymentId}&amp;hash={digest}',
            basicAuthUserName: Config::$instance->basicAuth->username,
            basicAuthPassword: Config::$instance->basicAuth->password,
            digestConfiguration: new DigestConfiguration(
                digestAlgorithm: 'SHA1',
                digestSalt: 'FOO',
                digestParameters: [
                    'paymentId'
                ]
            )
        );

        Repository::registerCallback(
            eventName: $eventName,
            request: $request
        );

        $registeredCallback = Repository::getCallback(eventName: $eventName);

        $deleteResponse = Repository::deleteCallback(eventName: $eventName);

        self::assertSame(
            expected: $eventName,
            actual: $registeredCallback->eventType
        );
        self::assertNotEmpty(
            actual: $registeredCallback->uriTemplate
        );
        self::assertSame(
            expected: 200,
            actual: $deleteResponse
        );
    }

    /**
     * Verify that fetching all registered callbacks works
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalTypeException
     * @throws ValidationException
     */
    public function testGetCallbacks(): void
    {
        if (Config::$instance->basicAuth === null) {
            self::fail(message: 'Basic auth is not configured.');
        }

        $eventNames = ['BOOKED', 'UPDATE'];
        $request = new Request(
            uriTemplate: 'https://example.com/dummy?id={paymentId}&amp;hash={digest}',
            basicAuthUserName: Config::$instance->basicAuth->username,
            basicAuthPassword: Config::$instance->basicAuth->password,
            digestConfiguration: new DigestConfiguration(
                digestAlgorithm: 'SHA1',
                digestSalt: 'FOO',
                digestParameters: [
                    'paymentId'
                ]
            )
        );

        foreach ($eventNames as $eventName) {
            Repository::registerCallback(
                eventName: $eventName,
                request: $request
            );
        }

        $response = Repository::getCallbacks();

        self::assertCount(
            expectedCount: 2,
            haystack: $response->toArray()
        );
    }

    /**
     * Verify that attempting to get an unregistered callback throws an EmptyValueException
     *
     * @return void
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalTypeException
     * @throws ValidationException
     */
    public function testGetCallbackFailure(): void
    {
        $this->expectException(
            exception: EmptyValueException::class
        );
        Repository::getCallback(eventName: 'UPDATE');
    }
}
