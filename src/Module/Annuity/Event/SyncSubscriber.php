<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Annuity\Event;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Lib\Event\SubscriberInterface;
use Resursbank\Ecom\Module\Annuity\Module;

use function is_string;

/**
 * Subscriber of Config::EVENT_SYNC_PAYMENT_METHOD event. Syncs Annuity factors
 * for each Payment Method that is synced to local storage.
 */
class SyncSubscriber implements SubscriberInterface
{
    /**
     * We require a none-empty string value keyed "method".
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        return (
            isset($data['method']) &&
            is_string($data['method']) &&
            $data['method'] !== ''
        );
    }

    /**
     * @param Config $config
     * @param array $data
     * @return void
     */
    public function execute(Config $config, array $data): void
    {
        if ($this->validate($data)) {
            $module = new Module($config);
            $module->sync(method: (string) $data['method']);
        }
    }
}
