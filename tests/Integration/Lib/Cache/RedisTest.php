<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Cache;

use PHPUnit\Framework\TestCase;
use RedisException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Cache\Redis;
use Exception;
use Redis as Server;

/**
 * Assert the Redis cache implementation works as expected.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RedisTest extends TestCase
{
    private const REDIS_HOST = 'redis';

    /**
     * @var Redis
     */
    private Redis $redis;

    /**
     * @var string
     */
    private string $key;

    /**
     * Setup filesystem cache instance.
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->redis = new Redis(host: self::REDIS_HOST);

        // NOTE: Simply using time() is unsafe, tests run too quickly.
        $this->key = AbstractCache::getKey(
            key: 'redis-cache-' . random_int(min: 0, max: 999999999) . time()
        );

        parent::setUp();
    }

    /**
     * @return Server
     * @throws RedisException
     */
    private function getRedisConnection(): Server
    {
        $server = new Server();
        $server->connect(host: self::REDIS_HOST);

        return $server;
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->write(
            key: 'It will not take spaces',
            data: '{cool:running}',
            ttl: 5
        );
    }

    /**
     * Assert ValidationException occurs when calling write() with an empty key.
     *
     * @return void
     */
    public function testWriteThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->write(key: '', data: 'StutterHat', ttl: 12);
    }

    /**
     * Assert that write() will pass without failure.
     *
     * @return void
     * @throws ValidationException
     * @throws RedisException
     */
    public function testWrite(): void
    {
        $data = 'basic data';

        $this->redis->write(key: $this->key, data: $data, ttl: 9999);

        self::assertEquals(
            expected: $data,
            actual: $this->getRedisConnection()->get(key: $this->key)
        );
    }

    /**
     * Assert that method read() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testReadThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->read(key: '?');
    }

    /**
     * Assert ValidationException occurs when calling read() with an empty key.
     *
     * @return void
     */
    public function testReadThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->read(key: '');
    }

    /**
     * Assert that read() method returns NULL if no valid data was found.
     *
     * @return void
     * @throws ValidationException
     */
    public function testReadReturnsNullForUndefinedData(): void
    {
        self::assertNull(actual: $this->redis->read(key: $this->key));
    }

    /**
     * Assert that read() can fetch data from Redis.
     *
     * @return void
     * @throws ValidationException
     * @throws RedisException
     */
    public function testRead(): void
    {
        $data = '9891823918391094850834523';

        $this->getRedisConnection()->set(key: $this->key, value: $data);

        self::assertEquals(
            expected: $data,
            actual: $this->redis->read(key: $this->key)
        );
    }

    /**
     * Assert that read() returns NULL when cached data has expired.
     *
     * @return void
     * @throws RedisException
     * @throws ValidationException
     */
    public function testReadReturnsNullForStaleData(): void
    {
        $data = 'testing a test';

        $conn = $this->getRedisConnection();

        $conn->setex(
            key: $this->key,
            expire: 2,
            value: $data
        );

        self::assertSame(expected: $data, actual: $conn->get(key: $this->key));

        sleep(seconds: 3);

        self::assertNull(actual: $this->redis->read(key: $this->key));
    }

    /**
     * Assert that method clear() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @return void
     */
    public function testClearThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->clear(key: 'HaHa#');
    }

    /**
     * Assert ValidationException occurs when calling clear() with an empty key.
     *
     * @return void
     */
    public function testClearThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->redis->clear(key: '');
    }

    /**
     * Assert that clear() will delete data from Redis.
     *
     * @return void
     * @throws ValidationException
     * @throws RedisException
     */
    public function testClear(): void
    {
        $data = 'Some crazy set of data.';

        $conn = $this->getRedisConnection();

        $conn->set(key: $this->key, value: $data);

        self::assertEquals(expected: $data, actual: $conn->get(key: $this->key));

        $this->redis->clear(key: $this->key);

        self::assertFalse(condition: $conn->get(key: $this->key));
    }
}
