<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\Widget\PaymentMethod;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Module\Widget\PaymentMethod\Css;

/**
 * Tests for the Payment Method CSS widget.
 */
class CssTest extends TestCase
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: $this->createMock(originalClassName: CacheInterface::class),
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
     * Verify that widget content is not empty and contains known selectors.
     */
    public function testContent(): void
    {
        $widget = new Css();
        $this->assertNotEmpty(actual: $widget->content);

        $definitions = [
            '.rb-payment-methods',
            '.rb-payment-methods th',
            '.rb-payment-methods td',
            '.rb-payment-methods table',
            '.rb-payment-methods thead',
            '.rb-payment-methods tbody',
            '.rb-payment-methods tbody tr:last-child',
            '.rb-payment-methods tbody tr:nth-child(even)',
            '.rb-payment-methods tbody tr:nth-child(odd)',
            '.rb-payment-methods-warning'
        ];

        foreach ($definitions as $definition) {
            $this->assertStringContainsString(
                needle: $definition,
                haystack: $widget->content
            );
        }
    }
}
