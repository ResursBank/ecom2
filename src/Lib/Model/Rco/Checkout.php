<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Locale\Rco\Locale;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Enum\AvailableActions;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CheckoutStatus;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;
use Resursbank\Ecom\Lib\Model\Rco\Enum\Currency;
use Resursbank\Ecom\Lib\Model\Rco\Enum\PaymentStatus;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

/**
 * Implementation of CheckoutDto object.
 *
 * This is similar to CreateCheckout but not the same, this object it utilized
 * for responses from the API when fetching the session.
 */
class Checkout extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        #[StringNotEmpty] #[StringIsUuid] public readonly string $id,
        #[StringNotEmpty] #[StringIsUuid] public readonly string $storeId,
        #[StringNotEmpty] #[StringMatchesRegex(
            pattern: '/^[a-zA-Z0-9]{1,32}$/'
        )]
        public readonly string $orderReference,
        public readonly CountryCode $countryCode,
        public readonly Locale $locale,
        public readonly Currency $currency,
        #[StringNotEmpty] #[StringIsUuid] public readonly string $version,
        public readonly Options $options,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly Cart $cart,
        public readonly Shipping $shipping,
        public readonly Payment $payment,
        public readonly Merchant $merchant,
        public readonly CheckboxCollection $checkboxes,
        public readonly string $notes,
        public readonly ?PaymentMethods $paymentMethods = null
    ) {
        parent::__construct();
    }

    /**
     * Checks if payment can be captured.
     */
    public function canCapture(): bool
    {
        return $this->canPerformAction(actionType: AvailableActions::CAPTURE);
    }

    /**
     * Checks if payment can be cancelled.
     */
    public function canCancel(): bool
    {
        return $this->canPerformAction(actionType: AvailableActions::CANCEL);
    }

    /**
     * Checks if payment can be refunded.
     */
    public function canRefund(): bool
    {
        return $this->canPerformAction(actionType: AvailableActions::REFUND);
    }

    /**
     * Checks if payment is processing (can be captured).
     */
    public function isProcessing(): bool
    {
        return $this->canCapture();
    }

    /**
     * Checks if payment has been captured.
     */
    public function isCaptured(): bool
    {
        return
            !$this->canCapture() &&
            (
                $this->payment->status->authorizedAmount -
                $this->payment->status->cancelledAmount -
                $this->payment->status->capturedAmount === 0
            ) &&
            $this->payment->status->capturedAmount > 0 &&
            $this->payment->status->capturedAmount !== $this->payment->status->refundedAmount;
    }

    /**
     * Checks if payment is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->status->type === CheckoutStatus::VERIFYING;
    }

    /**
     * Checks if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->payment->status->type === PaymentStatus::FAILED;
    }

    /**
     * Checks if payment has been cancelled.
     */
    public function isCancelled(): bool
    {
        return
            (
                $this->payment->status->authorizedAmount -
                $this->payment->status->cancelledAmount -
                $this->payment->status->capturedAmount === 0
            ) &&
            $this->payment->status->requestedAmount === $this->payment->status->cancelledAmount;
    }

    /**
     * Checks if payment has been refunded.
     */
    public function isRefunded(): bool
    {
        return
            $this->payment->status->capturedAmount > 0 &&
            (
                $this->payment->status->authorizedAmount -
                $this->payment->status->cancelledAmount -
                $this->payment->status->capturedAmount === 0
            ) &&
            $this->payment->status->capturedAmount === $this->payment->status->refundedAmount;
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return $this->currency !== Currency::EUR ? 'kr' : '€';
    }

    /**
     * Get currency format.
     */
    public function getCurrencyFormat(): CurrencyFormat
    {
        return $this->currency !== Currency::EUR ?
            CurrencyFormat::SYMBOL_LAST :
            CurrencyFormat::SYMBOL_FIRST;
    }

    /**
     * Check if specified action can be performed.
     */
    private function canPerformAction(AvailableActions $actionType): bool
    {
        foreach ($this->payment->status->availableActions as $action) {
            if ($action === $actionType) {
                return true;
            }
        }

        return false;
    }
}
