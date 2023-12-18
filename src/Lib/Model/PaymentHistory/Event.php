<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

/**
 * Available payment history events and their translation strings for the log.
 */
enum Event: string
{
    case CAPTURED = 'event-captured';
    case PARTIALLY_CAPTURED = 'event-partially-captured';
    case REFUNDED = 'event-refunded';
    case PARTIALLY_REFUNDED = 'event-partially-refunded';
    case CANCELLED = 'event-cancelled';
    case PARTIALLY_CANCELLED = 'event-partially-cancelled';
    case CREDIT_DENIED = 'event-credit-denied';
    case ABORTED = 'event-aborted';
    case CAPTURE_REQUESTED = 'event-capture-requested';
    case REFUND_REQUESTED = 'event-refund-requested';
    case CANCEL_REQUESTED = 'event-cancel-requested';
    case REQUEST_FAILED = 'event-request-failed';
}
