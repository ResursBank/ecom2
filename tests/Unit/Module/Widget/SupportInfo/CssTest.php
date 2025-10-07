<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Module\Widget\SupportInfo;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\SupportInfo\Css;

/**
 * Tests for SupportInfo CSS widget.
 */
class CssTest extends TestCase
{
    /**
     * Verify that the CSS is rendered.
     */
    public function testRenderCss(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(
            actual: $widget->content,
            message: 'Support Info widget CSS is empty'
        );

        $this->assertStringContainsString(
            needle: '.rb-si',
            haystack: $widget->content,
            message: 'Support Info widget CSS is missing the top element'
        );
    }

    /**
     * Verify that static template overrides work.
     *
     *  This test has been placed here rather than in the test class for the
     *  base Widget class as performing the test here makes for cleaner code
     *  since the base Widget class resides in Lib rather than Module and does
     *  not have any templates of its own to override.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testStaticTemplateOverride(): void
    {
        // Run setup.
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
            language: Language::SV,
            storeId: $_ENV['STORE_ID'],
            templateOverrideDirectory: '/tmp'
        );

        // Create override template.
        $overrideTemplate = '/tmp/SupportInfo/templates/css.css';

        if (file_exists($overrideTemplate)) {
            unlink($overrideTemplate);
        }

        if (!file_exists(dirname($overrideTemplate))) {
            mkdir(directory: '/tmp/SupportInfo/templates', recursive: true);
        }

        $templateContents = '<b>Override template</b>';
        file_put_contents(filename: $overrideTemplate, data: $templateContents);

        // Instantiate widget.
        $widget = new Css();

        // Confirm that override template is being used.
        $this->assertStringContainsString(
            needle: $templateContents,
            haystack: $widget->content
        );
    }
}
