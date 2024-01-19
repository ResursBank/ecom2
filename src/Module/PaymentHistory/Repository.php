<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Throwable;

/**
 * Repository layer against payment history persistent storage.
 */
class Repository
{
    /**
     * Connect to configured payment history storage and write info.
     *
     * @throws ConfigException
     */
    public static function write(
        Entry $entry
    ): void {
        Config::getPaymentHistoryDataHandler()->write(entry: $entry);
    }

    /**
     * Connect to configured payment history storage and get all log entries.
     *
     * @throws ConfigException
     */
    public static function getList(
        string $paymentId
    ): ?EntryCollection {
        return Config::getPaymentHistoryDataHandler()->getList(
            paymentId: $paymentId
        );
    }

    /**
     * Resolve formatted error message from Throwable.
     */
    public static function getError(
        Throwable $error
    ): string {
        $result = $error->getMessage() . "\n";
        $result .= $error->getFile() . ' :: ' . $error->getLine() . "\n";
        $result .= "--------------------------------------------------------------\n\n";
        $result .= $error->getTraceAsString();

        return $result;
    }
}
