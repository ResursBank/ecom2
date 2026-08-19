<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\GetStores;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\GetStores\Js;
use Resursbank\EcomTest\Utilities\DummySettingsReader;

/**
 * Tests for the GetStores widget.
 */
#[AllowMockObjectsWithoutExpectations]
class JsTest extends TestCase
{
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: new Filesystem(path: '/tmp/ecom-test/get_stores/' . time()),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            storeId: $_ENV['STORE_ID'],
            settingsReader: new DummySettingsReader()
        );
    }

    /**
     * Test widget behavior with all possible parameters.
     *
     * Render widget with as many parameters as possible, and check that the
     * expected JavaScript code is present in the rendered widget.
     *
     * @throws FilesystemException
     */
    public function testRenderMaximal(): void
    {
        $environmentSelectId = 'environment_select';
        $clientIdInputId = 'client_id_input';
        $clientSecretInputId = 'client_secret_input';
        $storeSelectId = 'store_select';
        $spinnerClass = 'spinner_class';

        $widget = new Js(
            automatic: true,
            storeSelectId: $storeSelectId,
            environmentSelectId: $environmentSelectId,
            clientIdInputId: $clientIdInputId,
            clientSecretInputId: $clientSecretInputId,
            spinnerClass: $spinnerClass
        );

        // Assert we attempt to resolve various elements using supplied id:s.
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $storeSelectId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $environmentSelectId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "storeSelect.parentElement.classList.add('" . $spinnerClass . "');",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $clientIdInputId . "')",
            haystack: $widget->content
        );
        $this->assertStringContainsString(
            needle: "document.getElementById('" . $clientSecretInputId . "')",
            haystack: $widget->content
        );

        // Confirm new Resursbank_FetchStores().setupEventListeners(); is not
        // present in the widget content.
        $this->assertStringContainsString(
            needle: 'new Resursbank_FetchStores().setupEventListeners();',
            haystack: $widget->content
        );
    }

    /**
     * Test widget behavior with minimal parameters.
     *
     * Render widget with as few parameters as possible, and check that the
     * expected JavaScript code is present in the rendered widget.
     */
    public function testRenderMinimal(): void
    {
        $widget = new Js();

        // Assert we do not attempt to resolve any elements.
        $this->assertStringNotContainsString(
            needle: "document.getElementById(",
            haystack: $widget->content
        );

        // Confirm new Resursbank_FetchStores().setupEventListeners(); is not
        //  present in the widget content.
        $this->assertStringNotContainsString(
            needle: 'new Resursbank_FetchStores().setupEventListeners();',
            haystack: $widget->content
        );
    }
}
