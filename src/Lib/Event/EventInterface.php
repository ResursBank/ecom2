<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Event;

/**
 * Describes an event.
 */
interface EventInterface
{
    /**
     * Get event identifier.
     */
    public function getName(): string;

    /**
     * Add an event subscriber.
     */
    public function addSubscriber(SubscriberInterface $subscriber): void;

    /**
     * Retrieve list of subscribers.
     *
     * NOTE: You are expected to validate the array of subscribers before
     * returning it. See the Event class for sample implementation. This is
     * because we cannot type hint the return value properly in PHP yet.
     *
     * @return array<SubscriberInterface>
     */
    public function getSubscribers(): array;
}
