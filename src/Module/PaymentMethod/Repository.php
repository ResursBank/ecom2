<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod;

use Error;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods;
use Exception;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Models\PaymentMethodCollection;
use TypeError;

use function is_array;
use function json_decode;

/**
 * Interaction with Payment Method entities and related functionality.
 */
class Repository
{
    /**
     * Cache key for GetPaymentMethods response.
     */
    public const CACHE_KEY = 'payment-methods';

    /**
     * Refresh cached data hourly.
     */
    public const CACHE_TTL = 3600;

    /**
     * @param string $storeId
     * @param GetPaymentMethods $api | DI to support testing.
     * @return PaymentMethodCollection
     * @throws ApiException
     * @throws CacheException
     */
    public static function getPaymentMethods(
        string $storeId,
        GetPaymentMethods $api = new GetPaymentMethods()
    ): PaymentMethodCollection {
        $result = self::readCache();

        if ($result === null) {
            $result = self::readApi(storeId: $storeId, api: $api);

            self::writeCache(data: $result->toArray());
        }

        return $result;
    }

    /**
     * @return PaymentMethodCollection|null
     * @throws CacheException
     */
    public static function readCache(): ?PaymentMethodCollection
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
                    targetType: PaymentMethod::class
                );

                if (
                    $cache instanceof PaymentMethodCollection &&
                    count($cache) > 0
                ) {
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
     * @param string $storeId
     * @param GetPaymentMethods $api
     * @return PaymentMethodCollection
     * @throws ApiException
     */
    public static function readApi(
        string $storeId,
        GetPaymentMethods $api = new GetPaymentMethods()
    ): PaymentMethodCollection {
        try {
            return $api->call(storeId: $storeId);
        } catch (Exception $e) {
            self::debug(
                cause: 'There was a problem reading data from Api.',
                exception: $e
            );

            throw new ApiException(
                message: 'Error while fetching payment methods from the API. ' .
                    'Please see debug log for more info.'
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
