<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Widget;

use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Test various widget methods.
 */
#[AllowMockObjectsWithoutExpectations]
final class WidgetTest extends TestCase
{
    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
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
            language: Language::EN,
            storeId: $_ENV['STORE_ID']
        );

        parent::setUp();
    }

    public function testGetTagNames(): void
    {
        $this->assertSame(
            expected: ['html', 'body', 'span', 'a', 'div'],
            actual: Widget::getTagNames(content:
                '<html><body><span><a href="testing">Testing</a><div class="famous">People walking</div></span></body>')
        );

        // Assert empty content generates empty array.
        $this->assertSame(
            expected: [],
            actual: Widget::getTagNames(content: '')
        );
    }

    public function testExceptionDuringRendering(): void
    {
        $widget = new Widget();
        $result = $widget->render(file: __DIR__ . 'fsdjiosefhuivnsjk');
        $this->assertEquals(expected: '', actual: $result);
    }

    /**
     * Verify that an empty string is returned of file doesn't exist.
     */
    public function testRenderStatic(): void
    {
        $widget = new Widget();
        $result = $widget->renderStatic(file: __DIR__ . 'fsdjiosefhuivnsjk');
        $this->assertEquals(expected: '', actual: $result);
    }

    /**
     * Verify that the dynamic data cache flag properly affects canCacheData().
     *
     * @throws ConfigException
     */
    public function testCanCacheData(): void
    {
        Config::setup();
        $unCachedWidget = new Widget();
        $this->assertFalse(
            condition: $unCachedWidget->canCacheData()
        );
    }

    /**
     * Verify correct behavior of getCacheKey.
     *
     * @throws ConfigException
     */
    public function testGetCacheKey(): void
    {
        Config::setup(storeId: $_ENV['STORE_ID']);
        $widget = new Widget();

        $this->assertEquals(
            expected: $widget::CACHE_KEY_PREFIX . '-' . Config::getStoreId() .
            '_' . sha1(string: serialize(value: $widget)),
            actual: $widget->getCacheKey()
        );
    }
}
