<?php

declare(strict_types=1);

namespace Resursbank\Ecom;

use Resursbank\Ecom\Api\Credentials;
use Resursbank\Ecom\Lib\Event\Event;
use Resursbank\Ecom\Lib\Event\Hub;
use Resursbank\Ecom\Lib\Mysql\Config as MysqlConfig;
use Resursbank\Ecom\Locale\Country;
use Resursbank\Ecom\Log\LoggerInterface;
use Resursbank\Ecom\Module\Annuity\Event\SyncSubscriber;
use Resursbank\Ecom\Simplified\Config as Simplified;
use Resursbank\Ecom\Aftershop\Config as Aftershop;

/**
 * API communication object.
 */
class Config
{
    /**
     * Event dispatched after syncing a payment method to local storage.
     */
    public const EVENT_SYNC_PAYMENT_METHOD = 'sync_payment_method';

    /**
     * @param Credentials $credentials
     * @param LoggerInterface $logger
     * @param Country $country
     * @param Simplified $simplified
     * @param Aftershop $aftershop
     * @param Hub $eventHub
     * @param MysqlConfig|null $mysqlCredentials | Metadata will be persisted to database if this is supplied.
     */
    public function __construct(
        public readonly Credentials $credentials,
        public readonly LoggerInterface $logger,
        public readonly Country $country,
        public readonly Simplified $simplified,
        public readonly Aftershop $aftershop,
        public readonly Hub $eventHub,
        public readonly null|MysqlConfig $mysqlCredentials = null
    ) {
        $this->configureEvents();
    }

    /**
     * @return void
     * @TODO Consider using attributes to setup events and listeners. This would require a cache implementation though.
     */
    public function configureEvents(): void
    {
        $this->eventHub->addEvent(
            event: new Event(
                self::EVENT_SYNC_PAYMENT_METHOD,
                [new SyncSubscriber()]
            )
        );
    }
}
