<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store;

use Error;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Exception;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Models\StoreCollection;
use Resursbank\Ecom\Module\Store\Api\GetStores;
use TypeError;

use function is_array;
use function json_decode;

/**
 * Business logic to interact with Store entities and related functionality.
 */
class Repository
{
    /**
     * Cache key for GetStores response.
     */
    public const CACHE_KEY = 'stores';

    /**
     * Refresh cached data hourly.
     */
    public const CACHE_TTL = 3600;

    /**
     * NOTE: GetStores DI to support testing.
     *
     * @param getStores $api
     * @return StoreCollection
     * @throws ApiException
     * @throws CacheException
     */
    public static function getStores(
        GetStores $api = new GetStores()
    ): StoreCollection {
        $result = self::readCache();

        if ($result === null) {
            $result = self::readApi(api: $api);

            self::writeCache(data: $result->toArray());
        }

        return $result;
    }

    /**
     * @return StoreCollection|null
     * @throws CacheException
     */
    public static function readCache(): ?StoreCollection
    {
        $result = null;

        $data = Config::$instance->cache->read(
            key: AbstractCache::getKey(key: self::CACHE_KEY)
        );

        try {
            if ($data !== null) {
                /**
                 * @psalm-suppress MixedAssignment
                 * @noinspection PhpRedundantOptionalArgumentInspection
                 */
                $data = json_decode(
                    json: $data,
                    associative: false,
                    depth: 512,
                    flags: JSON_THROW_ON_ERROR
                );

                if (!is_array(value: $data)) {
                    throw new IllegalTypeException(
                        message: 'Expected array got ' . gettype(value: $data)
                    );
                }
            }

            if (is_array(value: $data)) {
                /** @psalm-suppress MixedAssignment */
                $cache = DataConverter::arrayToCollection(
                    data: $data,
                    targetType: Store::class
                );

                if ($cache instanceof StoreCollection && count($cache) > 0) {
                    $result = $cache;
                }
            }
        } catch (TypeError | JsonException | ReflectionException | IllegalTypeException $e) {
            self::debug(
                cause: 'Corrupt cache data.',
                exception: $e,
                data: serialize(value: $data)
            );

            throw new CacheException(
                message: 'A problem occurred while reading data from ' .
                    'cache. Please see the debug log for more info.'
            );
        }

        return $result;
    }

    /**
     * NOTE: This method ends either with a valid dataset or an exception.
     * NOTE: $api is supplied through dependency injection to support testing.
     *
     * @param GetStores $api
     * @return StoreCollection
     * @throws ApiException
     */
    public static function readApi(
        GetStores $api = new GetStores()
    ): StoreCollection {
        try {
            return $api->call();
        } catch (Exception $e) {
            self::debug(
                cause: 'There was a problem reading data from Api.',
                exception: $e
            );

            throw new ApiException(
                message: 'Error while fetching stores from the API. Please ' .
                    'see debug log for more info.'
            );
        }
    }

    /**
     * @return void
     */
    public static function clearCache(): void
    {
        Config::$instance->cache->clear(
            key: AbstractCache::getKey(key: self::CACHE_KEY)
        );
    }

    /**
     * @param array $data
     * @throws CacheException
     */
    private static function writeCache(
        array $data,
    ): void {
        try {
            Config::$instance->cache->write(
                key: AbstractCache::getKey(key: self::CACHE_KEY),
                data: json_encode(value: $data, flags: JSON_THROW_ON_ERROR),
                ttl: self::CACHE_TTL
            );
        } catch (JsonException $e) {
            self::debug(
                cause: 'Failed to write to cache, corrupt data.',
                exception: $e,
                data: serialize(value: $data)
            );

            throw new CacheException(
                message: 'Failed writing to cache. See debug log for more info.'
            );
        }
    }

    /**
     * Write information to debug log.
     *
     * @param string $cause
     * @param Exception|Error $exception
     * @param string $data
     * @return void
     */
    private static function debug(
        string $cause,
        Exception|Error $exception,
        string $data = ''
    ): void {
        Config::$instance->logger->debug(message: '--------------------------');
        Config::$instance->logger->debug(message: $cause);
        Config::$instance->logger->debug(message: $exception);
        Config::$instance->logger->debug(message: serialize(value: $data));
        Config::$instance->logger->debug(message: '--------------------------');
    }
}
