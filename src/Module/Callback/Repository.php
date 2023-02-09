<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\CallbackInterface;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Throwable;

/**
 * Callback repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * @throws ConfigException
     */
    public static function process(
        CallbackInterface $callback,
        callable $process
    ): int {
        Config::getLogger()->debug(
            message: sprintf(
                'Processing callback %s for payment %s',
                $callback::class,
                $callback->getPaymentId()
            )
        );

        if ($callback instanceof Management) {
            Config::getLogger()->debug(
                message: "Action: {$callback->action->value} ($callback->actionId)"
            );
        }

        if ($callback instanceof Authorization) {
            Config::getLogger()->debug(
                message: "Status: {$callback->status->value}"
            );
        }

        $code = 202;

        try {
            $process($callback);
        } catch (Throwable $e) {
            self::logException(exception: $e);
            $code = 408;
        }

        Config::getLogger()->debug(message: "Responding with code $code");

        return $code;
    }
}
