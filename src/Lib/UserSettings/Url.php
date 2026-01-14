<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\UserSettings;

/**
 * Enum representing all URL:s utilised by various widgets in Ecom.
 */
enum Url: string
{
    case PART_PAYMENT_AJAX_URL = 'partPaymentAjaxUrl';
    case CALLBACK_TEST_TRIGGER_URL = 'callbackTestTriggerUrl';
    case CALLBACK_TEST_URL = 'callbackTestUrl';
    case CALLBACK_TEST_RECEIVED_AT_URL = 'callbackTestReceivedAtUrl';
    case CACHE_CLEAR_URL = 'cacheClearUrl';
}
