<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PartPayment\Traits;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\UserSettingsException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Log\Logger;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\UserSettings\Field;
use Resursbank\Ecom\Module\PriceSignage\Repository as SignageRepository;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Resursbank\Ecom\Module\UserSettings\Repository as UserSettingsRepository;
use Throwable;

/**
 * Common traits for the PartPayment widget classes.
 */
trait Common
{
    public ?float $threshold = null;

    /**
     * Populate properties with data from user settings if not directly supplied.
     *
     * @throws UserSettingsException
     */
    public function populateFromSettings(): void
    {
        $settings = UserSettingsRepository::getSettings();

        if ($this->paymentMethod === null) {
            $this->paymentMethod = Repository::getPartPaymentMethod();
        }

        if ($this->months === null) {
            $this->months = $settings->partPaymentPeriod;
        }

        $this->threshold = $settings->partPaymentThreshold;
    }

    /**
     * Fetch a Cost object from the Price signage API
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws Throwable
     */
    public function getCost(
        PaymentMethod $paymentMethod,
        float $amount,
        int $months
    ): Cost {
        $costs = SignageRepository::getPriceSignage(
            paymentMethodId: $paymentMethod->id,
            amount: $amount,
            monthFilter: $months
        );

        if (empty($costs->costList->toArray())) {
            throw new EmptyValueException(
                message: 'Returned CostCollection appears to be empty'
            );
        }

        if (sizeof($costs->costList) > 1) {
            throw new IllegalValueException(
                message: 'Returned CostCollection contains more than one Cost'
            );
        }

        return array_values(array: $costs->costList->toArray())[0];
    }

    /**
     * Check whether the current cost is eligible for part payment.
     */
    public function shouldDisplayCostExample(
        float $threshold,
        Cost $cost,
        PaymentMethod $paymentMethod,
        bool $showCostExample,
        ?float $amount = null
    ): bool {
        return
            ($threshold === 0.0 ||
                $cost->monthlyCost >= $threshold) &&
            (
                $amount === null ||
                $amount <= $paymentMethod->getMaxLimit()
            ) &&
            $paymentMethod->type !== Type::RESURS_INVOICE &&
            $showCostExample;
    }

    /**
     * Determine if widget should render.
     *
     * In order to render, this widget requires a payment method to have been
     * either supplied directly, or configured (note that the constructor will
     * attempt to populate from settings if not supplied). Additionally, part
     * payment must be enabled in user settings.
     */
    public function shouldRender(): bool
    {
        try {
            return
                $this->paymentMethod !== null &&
                UserSettingsRepository::isEnabled(
                    field: Field::PART_PAYMENT_ENABLED
                )
            ;
        } catch (Throwable $error) {
            Logger::error(message: $error);
        }

        return false;
    }
}
