<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Log\LogLevel;

/**
 * These are settings defined by the user (integration), such as API credentials,
 * part payment method / annuity factor, swish limits etc.
 */
class UserSettings extends Model
{
    public const DEFAULT_API_TIMEOUT = 30;

    /**
     * @param bool $enabled - Whether Resurs Bank integration is enabled
     * @param string|null $clientIdProd - MAPI Client ID (production)
     * @param string|null $clientSecretProd - MAPI Client Secret (production)
     * @param string|null $clientIdTest - MAPI Client ID (test)
     * @param string|null $clientSecretTest - MAPI Client Secret (test)
     * @param string|null $storeId - MAPI Store ID
     * @param Environment $environment - MAPI Environment
     * @param int $apiTimeout - Timeout for API requests in seconds | Default: 30
     * @param bool $logEnabled - Enable or disable logging
     * @param LogLevel $logLevel - Log level | Default: INFO
     * @param PaymentMethod|null $partPaymentMethod - Payment method to use for installment calculations
     * @param float|null $partPaymentThreshold - Lowest amount for part payment widget to render
     * @param int|null $partPaymentPeriod - Part payment period in months (int)
     * @param bool $partPaymentLegacyLinks - Whether to use legacy links in part payment widget
     * @param bool $partPaymentShowCostExample - Whether to render cost examples in the part payment widget
     * @param bool $enableGetAddress - Enable or disable get address widget render
     * @param bool $captureEnabled - Enable or disable capture
     * @param bool $refundEnabled - Enable or disable refund
     * @param bool $cancelEnabled - Enable or disable cancel
     * @param bool $modifyEnabled - Enable or disable payment modification
     * @param bool $paymentHistoryEnabled - Enable or disable payment history tracking & widget rendering
     * @param bool $partPaymentEnabled - Enable or disable part payment widget rendering
     * @param bool $cacheEnabled - Enable or disable caching of API responses etc.
     * @param bool $developerMode - Enable or disable developer mode
     * @param bool $handleFrozenPayments - Whether to automatically handle frozen payments
     * @param bool $handleManualInspection - Whether to automatically handle payments in manual inspection
     * @param string|null $xdebugSessionValue - XDEBUG_SESSION cookie value to propagate in API requests
     * @param int|null $testTriggeredAt - Timestamp when last test callback was triggered
     * @param int|null $testReceivedAt - Timestamp when last test callback was received
     * @param int|null $swishMaxLimit - Maximum limit for Swish payments
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     * @todo Could add attribute to validate $logDir value is a valid directory path.
     */
    public function __construct(
        public readonly bool $enabled = true,
        #[StringNotEmpty] public readonly ?string $clientIdProd = null,
        #[StringNotEmpty] public readonly ?string $clientSecretProd = null,
        #[StringNotEmpty] public readonly ?string $clientIdTest = null,
        #[StringNotEmpty] public readonly ?string $clientSecretTest = null,
        public readonly ?string $logDir = null,
        #[StringIsUuid] public readonly ?string $storeId = null,
        public readonly Environment $environment = Environment::TEST,
        public readonly int $apiTimeout = self::DEFAULT_API_TIMEOUT,
        public readonly bool $logEnabled = true,
        public readonly LogLevel $logLevel = LogLevel::INFO,
        public readonly ?PaymentMethod $partPaymentMethod = null,
        public readonly ?float $partPaymentThreshold = null,
        public readonly ?int $partPaymentPeriod = null,
        public readonly bool $partPaymentLegacyLinks = false,
        public readonly bool $partPaymentShowCostExample = true,
        public readonly bool $enableGetAddress = true,
        public readonly bool $captureEnabled = true,
        public readonly bool $refundEnabled = true,
        public readonly bool $cancelEnabled = true,
        public readonly bool $modifyEnabled = true,
        public readonly bool $paymentHistoryEnabled = true,
        public readonly bool $partPaymentEnabled = false,
        public readonly bool $cacheEnabled = true,
        public readonly bool $developerMode = false,
        public readonly bool $handleFrozenPayments = false,
        public readonly bool $handleManualInspection = false,
        public readonly ?string $xdebugSessionValue = null,
        public readonly ?int $testTriggeredAt = null,
        public readonly ?int $testReceivedAt = null,
        public readonly ?int $swishMaxLimit = null,
    ) {
        parent::__construct();
    }
}
