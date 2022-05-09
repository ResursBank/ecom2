<?php 

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\EventException;
use Resursbank\Ecom\Exception\EventSubscriberException;
use Resursbank\Ecom\Module\Module as CoreModule;

/**
 * Business logic to interact with Payment Method entities and related
 * functionality.
 */
class Module extends CoreModule
{
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
