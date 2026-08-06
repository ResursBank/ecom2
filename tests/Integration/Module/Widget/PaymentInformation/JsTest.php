<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentInformation;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\PaymentInformation\Js;

/**
 * Tests for Payment information JS widget.
 */
#[AllowMockObjectsWithoutExpectations]
class JsTest extends TestCase
{
    /**
     * @throws JsonException
     * @throws Exception
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                type: LoggerInterface::class
            ),
            cache: $this->createMock(type: CacheInterface::class),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            ),
            language: Language::SV,
            storeId: $_ENV['STORE_ID']
        );
    }

    /**
     * Verify that template is rendered.
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function testContent(): void
    {
        $amount = 100.00;
        $reloadUrl = 'https://example.com/';
        $widgetElement = 'sampleWidgetElement';
        $amountElement = 'sampleAmountElement';
        $observableElements = [];
        $widget = new Js(
            amount: $amount,
            reloadUrl: $reloadUrl,
            widgetElement: $widgetElement,
            amountElement: $amountElement,
            observableElements: $observableElements
        );

        $strings = [
            'lastAmount = ' . $amount,
            'await fetch(\'' . $reloadUrl . '\', {',
            'this.el = document.querySelector(\'' . $widgetElement . '\');',
            'const elDom = \'' . $amountElement . '\';'
        ];

        foreach ($strings as $string) {
            $this->assertStringContainsString(
                needle: $string,
                haystack: $widget->content
            );
        }
    }
}
