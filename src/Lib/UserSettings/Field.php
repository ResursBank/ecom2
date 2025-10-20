<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\UserSettings;

/**
 * Enum representing all user settings fields.
 *
 * The value of each enum case corresponds to the parameter name in the
 * UserSettings model constructor.
 *
 * The reader handles the enum cases, and uses the values to map to the correct
 * property on the UserSettings model.
 */
enum Field: string
{
    case ENABLED = 'enabled';
    case CLIENT_ID_PROD = 'clientIdProd';
    case CLIENT_SECRET_PROD = 'clientSecretProd';
    case CLIENT_ID_TEST = 'clientIdTest';
    case CLIENT_SECRET_TEST = 'clientSecretTest';
    case STORE_ID = 'storeId';
    case ENVIRONMENT = 'environment';
    case API_TIMEOUT = 'apiTimeout';
    case LOG_ENABLED = 'logEnabled';
    case LOG_LEVEL = 'logLevel';
    case PART_PAYMENT_METHOD = 'partPaymentMethod';
    case PART_PAYMENT_THRESHOLD = 'partPaymentThreshold';
    case PART_PAYMENT_PERIOD = 'partPaymentPeriod';
    case PART_PAYMENT_LEGACY_LINKS = 'partPaymentLegacyLinks';
    case PART_PAYMENT_SHOW_COST_EXAMPLE = 'partPaymentShowCostExample';
    case ENABLE_GET_ADDRESS = 'enableGetAddress';
    case CAPTURE_ENABLED = 'captureEnabled';
    case REFUND_ENABLED = 'refundEnabled';
    case CANCEL_ENABLED = 'cancelEnabled';
    case MODIFY_ENABLED = 'modifyEnabled';
    case PAYMENT_HISTORY_ENABLED = 'paymentHistoryEnabled';
    case CACHE_ENABLED = 'cacheEnabled';
    case PART_PAYMENT_ENABLED = 'partPaymentEnabled';
    case DEVELOPER_MODE = 'developerMode';
    case HANDLE_FROZEN_PAYMENTS = 'handleFrozenPayments';
    case HANDLE_MANUAL_INSPECTION = 'handleManualInspection';
    case XDEBUG_SESSION_VALUE = 'xdebugSessionValue';
    case TEST_TRIGGERED_AT = 'testTriggeredAt';
    case TEST_RECEIVED_AT = 'testReceivedAt';
    case SWISH_MAX_LIMIT = 'swishMaxLimit';
    case LOG_DIR = 'logDir';
}
