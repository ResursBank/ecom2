<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\Payment\Api\Traits\Shared;
use Resursbank\Ecom\Module\Payment\Repository;
use Throwable;

/**
 * POST /payments/{payment_id}/cancel
 */
class Cancel
{
    use Shared;

    /**
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function call(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null
    ): Payment {
        $this->logRequest(
            paymentId: $paymentId,
            event: Event::CANCEL_REQUESTED
        );

        $payload = [];

        $previouslyCancelled = $this->getCanceledAmount(paymentId: $paymentId);

        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }

        if ($creator) {
            $payload['creator'] = $creator;
        }

        $result = $this->getResponse(paymentId: $paymentId, payload: $payload);

        $cancelled = $this->getCanceledAmount(paymentId: $paymentId)
            - $previouslyCancelled;

        $this->logSuccess(
            paymentId: $paymentId,
            event: $result->isCancelled() ? Event::CANCELED : Event::PARTIALLY_CANCELLED,
            extra: Price::format(value: $cancelled)
        );

        return $result;
    }

    /**
     * Get canceled amount.
     *
     * @throws ConfigException
     */
    public function getCanceledAmount(string $paymentId): float
    {
        try {
            $payment = Repository::get(paymentId: $paymentId);
            return (float) $payment->order?->canceledAmount;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return 0.0;
    }

    /**
     * Call API and process response.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws FilesystemException
     * @throws TranslationException
     */
    private function getResponse(string $paymentId, array $payload): Payment
    {
        $curl = $this->getCurlObject(
            paymentId: $paymentId,
            method: 'cancel',
            payload: $payload
        );
        $data = $curl->exec()->body;

        return $this->processResponse(paymentId: $paymentId, response: $data);
    }
}
