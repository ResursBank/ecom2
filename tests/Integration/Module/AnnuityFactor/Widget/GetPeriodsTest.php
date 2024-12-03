<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\AnnuityFactor\Widget;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\AnnuityFactor\Widget\GetPeriods;
use Throwable;

/**
 * Integration test for the Get Periods widget.
 */
class GetPeriodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
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

        $widget = new GetPeriods(
            storeId: $_ENV['STORE_ID'],
            methodElementId: $methodElementId,
            periodElementId: $periodElementId,
            automatic: false
        );

        // Confirm class Resursbank_GetPeriods exists.
        $this->assertStringContainsString(
            needle: 'Resursbank_GetPeriods',
            haystack: $widget->js
        );

        // Confirm result = document.getElementById('$this->methodElementId')
        // is rendered.
        $this->assertStringContainsString(
            needle: "result = document.getElementById('" . $methodElementId . "')",
            haystack: $widget->js
        );

        // Confirm result = document.getElementById('$this->periodElementId')
        // is rendered.
        $this->assertStringContainsString(
            needle: "result = document.getElementById('" . $periodElementId . "')",
            haystack: $widget->js
        );

        // Confirm that "document.addEventListener(" followed by
        // "DOMContentLoaded" before the next ");" is not present since
        // automatic is set to false.
        $this->assertDoesNotMatchRegularExpression(
            pattern: '/document\.addEventListener\([^)]*DOMContentLoaded[^)]*\)/',
            string: $widget->js,
            message: "Automatic widget initialization is not present in the widget content."
        );

        // Widget without elements.
        $widgetNoElements = new GetPeriods(
            storeId: $_ENV['STORE_ID'],
            automatic: true
        );

        // Confirm result = document.getElementById('$this->methodElementId')
        // isn't rendered.
        $this->assertStringNotContainsString(
            needle: "result = document.getElementById('" . $methodElementId . "')",
            haystack: $widgetNoElements->js
        );

        // Confirm result = document.getElementById('$this->periodElementId')
        // isn't rendered.
        $this->assertStringNotContainsString(
            needle: "result = document.getElementById('" . $periodElementId . "')",
            haystack: $widgetNoElements->js
        );

        // Confirm that "document.addEventListener(" followed by
        // "DOMContentLoaded" before the next ");" is present since
        // automatic is set to true.
        $this->assertMatchesRegularExpression(
            pattern: '/document\.addEventListener\([^)]*DOMContentLoaded[^)]*\)/',
            string: $widgetNoElements->js,
            message: "Automatic widget initialization is not present in the widget content."
        );
    }

    /**
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
        $widget = new GetPeriods(storeId: $_ENV['STORE_ID'], automatic: false);

        $jsonData = $widget->getJsonData();

        $this->assertNotEmpty($jsonData);

        try {
            $data = json_decode(
                json: $jsonData,
                associative: true,
                depth: 512,
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
}
