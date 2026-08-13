<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\GetPeriods;

use Exception;
use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Ecom\Module\Widget\GetPeriods\Js;
use Throwable;

/**
 * Integration test for the Get Periods widget.
 */
#[AllowMockObjectsWithoutExpectations]
class JsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Assert widget content (JS) is properly rendered.
     *
     * This tests asserts that:
     *
     * - The JS widget class is present.
     * - Data is automatically collected from elements, if provided.
     * - Data is not automatically collected from elements, if not provided.
     * - Automatic widget initialization is present when requested.
     * - Automatic widget initialization is not present when not requested.
     */
    public function testRenderHtml(): void
    {
        $methodElementId = 'method-el';
        $periodElementId = 'period-el';

        $widget = new Js(
            methodElementId: $methodElementId,
            periodElementId: $periodElementId,
            automatic: false
        );

        // Confirm class Resursbank_GetPeriods exists.
        $this->assertStringContainsString(
            needle: 'Resursbank_GetPeriods',
            haystack: $widget->content
        );

        // Confirm result = document.getElementById('$this->methodElementId')
        // is rendered.
        $this->assertStringContainsString(
            needle: "result = document.getElementById('" . $methodElementId . "')",
            haystack: $widget->content
        );

        // Confirm result = document.getElementById('$this->periodElementId')
        // is rendered.
        $this->assertStringContainsString(
            needle: "result = document.getElementById('" . $periodElementId . "')",
            haystack: $widget->content
        );

        // Confirm that "document.addEventListener(" followed by
        // "DOMContentLoaded" before the next ");" is not present since
        // automatic is set to false.
        $this->assertDoesNotMatchRegularExpression(
            pattern: '/document\.addEventListener\([^)]*DOMContentLoaded[^)]*\)/',
            string: $widget->content,
            message: "Automatic widget initialization is not present in the widget content."
        );

        // Widget without elements.
        $widgetNoElements = new Js(automatic: true);

        // Confirm result = document.getElementById('$this->methodElementId')
        // isn't rendered.
        $this->assertStringNotContainsString(
            needle: "result = document.getElementById('" . $methodElementId . "')",
            haystack: $widgetNoElements->content
        );

        // Confirm result = document.getElementById('$this->periodElementId')
        // isn't rendered.
        $this->assertStringNotContainsString(
            needle: "result = document.getElementById('" . $periodElementId . "')",
            haystack: $widgetNoElements->content
        );

        // Confirm that "document.addEventListener(" followed by
        // "DOMContentLoaded" before the next ");" is present since
        // automatic is set to true.
        $this->assertMatchesRegularExpression(
            pattern: '/document\.addEventListener\([^)]*DOMContentLoaded[^)]*\)/',
            string: $widgetNoElements->content,
            message: "Automatic widget initialization is not present in the widget content."
        );
    }

    /**
     * Verify getJsonData behavior.
     *
     * Verify getJsonData() returns none-empty JSON data (should be decoded to
     * an array).
     *
     * This tests confirms:
     *
     * - The JSON data is not empty.
     * - The JSON data is decoded to an array.
     * - The array is not empty.
     * - Array is associative.
     * - Each inner element is another array, containing key => value pairs
     * indicating month -> annuity factor mappings.
     */
    public function testGetJsonData(): void
    {
        $widget = new Js(automatic: false);

        $jsonData = $widget->getJsonData();

        $this->assertNotEmpty($jsonData);

        try {
            $data = json_decode(
                json: $jsonData,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $this->assertIsArray($data);

            // Confirm the array is not empty.
            $this->assertNotEmpty($data);

            // Loop through the element and make sure each element is an array.
            foreach ($data as $methodId => $element) {
                $this->assertNotEmpty($methodId);
                $this->assertIsArray($element);

                // Confirm $element is not empty.
                $this->assertNotEmpty($element);

                // Loop through $element, expect none-empty int / string
                // structures.
                foreach ($element as $key => $value) {
                    $this->assertIsInt($key);
                    $this->assertNotEmpty($key);
                    $this->assertIsString($value);
                    $this->assertNotEmpty($value);
                }
            }
        } catch (Throwable $error) {
            $this->fail(
                message: 'Failed to parse JSON periods data: ' . $error->getMessage()
            );
        }
    }

    /**
     * Verify that exception is handled by getJsonData.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testGetJsonDataException(): void
    {
        unlink(filename: '/tmp/ecom.log');
        Config::setup(
            logger: new FileLogger(path: '/tmp'),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID']
        );

        $mockedJs = $this
            ->getMockBuilder(className: Js::class)
            ->onlyMethods(methods: ['getAnnuityFactorsForMethod'])
            ->getMock();
        $mockedJs->method('getAnnuityFactorsForMethod')
            ->willThrowException(
                exception: new Exception(message: 'fake error')
            );

        $mockedJs->getJsonData();

        $fileContents = file_get_contents('/tmp/ecom.log');

        if ($fileContents === false) {
            unlink(filename: '/tmp/ecom.log');
            $this->fail();
        }

        if (
            str_contains(haystack: $fileContents, needle: 'fake error')
        ) {
            $this->addToAssertionCount(count: 1);
            unlink(filename: '/tmp/ecom.log');
            return;
        }

        unlink(filename: '/tmp/ecom.log');
        $this->fail();
    }

    /**
     * Verify the output of getJsonPaymentMethods.
     *
     * @throws JsonException
     */
    public function testGetJsonPaymentMethods(): void
    {
        $widget = new Js();

        $jsonPaymentMethods = $widget->getJsonPaymentMethods();

        $this->assertNotEmpty(actual: $jsonPaymentMethods);
        $this->assertIsString(actual: $jsonPaymentMethods);

        try {
            $decoded = json_decode(
                json: $jsonPaymentMethods,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
            $this->addToAssertionCount(count: 1);
        } catch (Throwable $error) {
            $this->fail(
                message: 'Unable to parse JSON data: ' .
                $error->getMessage()
            );
        }

        $this->assertIsArray(actual: $decoded);

        foreach ($decoded as $methodId => $method) {
            $this->assertIsString(actual: $methodId);
            $this->assertTrue(
                condition: Strings::isUuid(value: $methodId)
            );
            $this->assertIsArray(actual: $method);
            $this->assertArrayHasKey(key: 'id', array: $method);
            $this->assertArrayHasKey(key: 'name', array: $method);
            $this->assertEquals(expected: $methodId, actual: $method['id']);
            $this->assertNotEmpty(actual: $method['name']);
        }
    }

    /**
     * Verify the output of getAnnuityFactorsForMethod.
     */
    public function testGetAnnuityFactorsForMethod(): void
    {
        $widget = new Js();

        try {
            $paymentMethod = Repository::getById(
                paymentMethodId: $_ENV['ANNUITY_PAYMENT_METHOD_ID']
            );
        } catch (Throwable $error) {
            $this->fail(
                message: 'Unable to fetch payment method: ' .
                $error->getMessage()
            );
        }

        $this->assertNotNull(actual: $paymentMethod);

        try {
            $annuityFactors = $widget->getAnnuityFactorsForMethod(
                method: $paymentMethod
            );
        } catch (Throwable $error) {
            $this->fail(
                message: 'Unable to fetch annuity factors for method: ' .
                $error->getMessage()
            );
        }

        $this->assertNotEmpty(actual: $annuityFactors);

        foreach ($annuityFactors as $period => $annuityFactor) {
            $this->assertIsInt(actual: $period);
            $this->assertIsString(actual: $annuityFactor);
        }
    }
}
