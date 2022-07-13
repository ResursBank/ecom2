<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\EventException;
use Resursbank\Ecom\Exception\EventSubscriberException;
use Resursbank\Ecom\Module\Module as CoreModule;
use function is_array;

/**
 * Business logic to interact with Payment Method entities and related
 * functionality.
 */
class Module extends CoreModule
{
    public static function read(): array
    {
        $data = self::readCache();

        if (!is_array($data)) {
            $data = self::readDb();

            if (!is_array($data)) {
                $data = self::readApi();

                self::writeToDb($data);
            }

            self::writeCache($data);
        }

        return [];
    }

    public static function clearCache(): void
    {
        // Drop data from cache.
    }

    private static function readCache(): ?array
    {
        $data = null;

        // 1. If there was a cache driver defined, read data from cache Config::getCache()->read('payment_methods')
        // 2. If data was returned from cache then do $data = json_decode($cacheData)
        // 3. Check if data is stale, then set $data = null

        return is_array($data) && count($data) > 0 ? $data : null;
    }

    private static function readDb(): ?array
    {
        $data = null;

        // 1. If there was a database driver defined, read data from db Config::getDb()->read('payment_methods')
        // 2. If data was returned from db then do $data = json_decode($dbData)
        // 3. Check if data is stale, then set $data = null

        return is_array($data) && count($data) > 0 ? $data : null;
    }

    private static function readApi(): ?array
    {

    }

    /**
     * @return void
     * @throws EventException
     * @throws EventSubscriberException
     * @throws JsonException
     */
    public function sync(): void
    {
        // @TODO Implement business logic to sync payment methods.
        // 1. $methods = fetch_methods_as_array
        // 2. Convert methods to PaymentMethod Entity instances.
        // 3. Foreach -> Store in DB and execute below event, setting 'method' to Payment Method identifier.
        $this->config->eventHub->dispatch(
            name: Config::EVENT_SYNC_PAYMENT_METHOD,
            data: [
                'method' => ''
            ]
        );
    }
}
