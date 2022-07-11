<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\RcoCallback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Module\Module as CoreModule;
use Resursbank\Ecom\Module\RcoCallback\Models\Callback;
use Resursbank\Ecom\Module\RcoCallback\Models\CallbackCollection;
use Resursbank\Ecom\Module\RcoCallback\Api\GetCallback;
use Resursbank\Ecom\Module\RcoCallback\Api\GetCallbacks;
use Resursbank\Ecom\Module\RcoCallback\Api\DeleteCallback;
use Resursbank\Ecom\Module\RcoCallback\Api\RegisterCallback;
use Resursbank\Ecom\Module\RcoCallback\Models\RegisterCallback\Request;

/**
 * Main entrypoint for interfacing with the RCO callback API programmatically
 */
class Repository extends CoreModule
{
    public const HOSTNAME_PROD = 'checkout.resurs.com';
    public const HOSTNAME_TEST = 'omnitest.resurs.com';

    /**
     * @param string $eventName
     * @param Request $request
     * @return void
     * @throws JsonException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    public static function registerCallback(string $eventName, Request $request): void
    {
        (new RegisterCallback())
            ->call(eventName: $eventName, request: $request);
    }

    /**
     * @param string $eventName
     * @return Callback
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     * @throws ReflectionException
     */
    public static function getCallback(string $eventName): Callback
    {
        return (new GetCallback())
            ->call(eventName: $eventName);
    }

    /**
     * @return CallbackCollection
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function getCallbacks(): CallbackCollection
    {
        return (new GetCallbacks())
            ->call();
    }

    /**
     * @param string $eventName
     * @return int
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function deleteCallback(string $eventName): int
    {
        return (new DeleteCallback())
            ->call(eventName: $eventName);
    }

    /**
     * Gets API hostname
     *
     * @return string
     */
    public static function getApiHostname(): string
    {
        if (Config::$instance->isProduction) {
            return self::HOSTNAME_PROD;
        }

        return self::HOSTNAME_TEST;
    }
}
