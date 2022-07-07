<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\RcoCallback;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Network\Model\Auth\Basic;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\DigestConfiguration;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\Request;
use Resursbank\Ecom\Module\RcoCallback\Repository;

class RepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws \Resursbank\Ecom\Exception\Validation\EmptyValueException
     */
    protected function setUp(): void
    {
        // Set up Config object
        Config::setup(
            logger: $this->createMock(originalClassName: FileLogger::class),
            basicAuth: new Basic(username: $_ENV['BASIC_AUTH_USERNAME'], password: $_ENV['BASIC_AUTH_PASSWORD'])
        );

        // Clear existing callbacks
        $eventNames = ['TEST', 'UNFREEZE', 'BOOKED'];
        foreach ($eventNames as $eventName) {
            Repository::deleteCallback(eventName: $eventName);
        }
    }

    /**
     * Verify that we can register, fetch and delete callbacks
     *
     * @return void
     */
    public function testRegisterGetAndDeleteCallback(): void
    {
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

        $this->assertEquals(
            expected: $eventName,
            actual: $registeredCallback->eventType
        );
        $this->assertNotEmpty(
            actual: $registeredCallback->uriTemplate
        );
        $this->assertEquals(
            expected: 200,
            actual: $deleteResponse
        );
    }

    public function testGetCallbacks(): void
    {
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
        
        $this->assertCount(
            expectedCount: 2,
            haystack: $response->toArray()
        );
    }
}
