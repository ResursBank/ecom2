<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentInformation;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\PaymentInformation\Css;

/**
 * Tests for the payment information CSS widget.
 */
#[AllowMockObjectsWithoutExpectations]
class CssTest extends TestCase
{
    /**
     * @throws EmptyValueException
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
     * Confirm that widget content is not empty.
     */
    public function testContent(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(actual: $widget->content);

        $definitions = [
            '.rb-pi',
            '.rb-pi table',
            '.rb-pi table th',
            '.rb-pi table th svg',
            '.rb-pi table td',
            '.rb-pi table td.rb-pi-row-header',
            '.rb-pi table td:not(.rb-pi-row-header)',
            '.rb-pi table tr:nth-child(even)',
            '.rb-pi table tr:nth-child(odd)'
        ];

        foreach ($definitions as $definition) {
            $this->assertStringContainsString(
                needle: $definition,
                haystack: $widget->content
            );
        }
    }
}
