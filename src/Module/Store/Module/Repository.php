<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Module\Store\Api\GetStores;
use Exception;
use Resursbank\Ecom\Module\Store\Model\Store;
use stdClass;

use function is_array;
use function json_decode;

/**
 * Business logic to interact with Store entities and related functionality.
 */
class Repository
{
    /**
     * Stores JSON encoded API response with stores.
     */
    public const CACHE_KEY = 'stores';

    /**
     * Refresh cached store data hourly.
     */
    public const CACHE_TTL = 3600;

    /**
     * @param bool $silent | Write Exceptions to debug log then suppress them.
     * @return null|array
     * @throws CacheException
     * @throws ApiException
     */
    public static function read(
        bool $silent = true
    ): ?array {
        $result = null;

        try {
            $result = self::readCache(silent: $silent);

            if ($result === null) {
                $result = self::readApi(silent: $silent);
            }
        } catch (CacheException | ApiException $e) {
            if (!$silent) {
                throw $e;
            }
        }

        return $result;
    }

    /**
     * NOTE: Exceptions can only occur if $silent is assigned false.
     *
     * @param bool $silent | Do not throw Exceptions upstream after logging.
     * @return array|null
     * @throws CacheException
     */
    public static function readCache(
        bool $silent = true
    ): ?array {
        $result = [];

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
            }

            if (is_array(value: $data)) {
                $result = self::parseData(data: $data);
            }
        } catch (JsonException | ValidationException | ReflectionException $e) {
            self::debug(
                cause: 'Corrupt cache data.',
                exception: $e,
                data: serialize(value: $data)
            );

            if (!$silent) {
                throw new CacheException(
                    message: 'A problem occurred while reading data from ' .
                        'cache. Please see the debug log for more info.'
                );
            }
        }

        return count($result) > 0 ? $result : null;
    }

    /**
     * NOTE: Exceptions can only occur if $silent is assigned false.
     *
     * @param Request $request
     * @param bool $silent | Do not throw Exceptions upstream after logging.
     * @return array|null
     * @throws ApiException
     * @todo At the time of writing stubs cannot be initialized
     */
    public static function readApi(
        GetPaymentMethods $api = new GetPaymentMethods(),
        bool $silent = true
    ): ?array {
        $result = null;

        try {
            $result = $api->exec();
            ///$result = $request->execute()->getData();

            die(var_dump($result));

            if ($result !== null && count($result)) {
                self::writeCache(data: $result, silent: $silent);
                $result = self::parseData(data: $result);
            }
        } catch (Exception $e) {
            self::debug(
                cause: 'There was a problem reading data from Api.',
                exception: $e,
                data: serialize(value: $result)
            );

            if (!$silent) {
                throw new ApiException(
                    message: 'Error while fetching payment methods from the ' .
                        'API. Please see debug log for mor info.'
                );
            }
        }

        return $result;
    }

    /**
     * Write information to debug log.
     *
     * @param string $cause
     * @param Exception $exception
     * @param string $data
     * @return void
     */
    private static function debug(
        string $cause,
        Exception $exception,
        string $data
    ): void {
        Config::$instance->logger->debug(message: '--------------------------');
        Config::$instance->logger->debug(message: $cause);
        Config::$instance->logger->debug(message: $exception);
        Config::$instance->logger->debug(message: serialize(value: $data));
        Config::$instance->logger->debug(message: '--------------------------');
    }

    /**
     * @param array $data
     * @param bool $silent | Do not throw Exceptions upstream after logging.
     * @throws CacheException
     */
    private static function writeCache(
        array $data,
        bool $silent = true
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
        }

        if (!$silent) {
            throw new CacheException(
                message: 'Failed writing to cache. See debug log for more info.'
            );
        }
    }

    /**
     * Parse data from cache / API request to entity collection.
     *
     * @param array $data
     * @param ArrayValidation $arrayValidation
     * @return array
     * @throws ValidationException
     * @throws ReflectionException
     * @todo When the Collection base class is completed we should utilize that instead of an array.
     */
    private static function parseData(
        array $data,
        ArrayValidation $arrayValidation = new ArrayValidation()
    ): array {
        $result = [];

        // Make sure array is sequential.
        $arrayValidation->isSequential(data: $data);

        // Make sure array consists of arrays.
        $arrayValidation->isStdClassCollection(data: $data);

        /** @var stdClass $item */
        foreach ($data as $item) {
            $result[] = DataConverter::stdClassToType(
                object: $item,
                type: Method::class
            );
        }

        return $result;
    }
}
