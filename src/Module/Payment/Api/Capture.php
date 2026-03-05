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
 * POST /payments/{payment_id}/capture
 */
class Capture
{
    use Shared;

    /**
     * Makes call to the API
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
    public function call(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): Payment {
        $this->logRequest(
            paymentId: $paymentId,
            event: Event::CAPTURE_REQUESTED
        );

        $previouslyCaptured = $this->getCapturedAmount(paymentId: $paymentId);

        $payload = $this->getPayload(
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId,
            invoiceId: $invoiceId
        );

        $curl = $this->getCurlObject(
            paymentId: $paymentId,
            method: 'capture',
            payload: $payload
        );
        $data = $curl->exec()->body;

        $result = $this->processResponse(
            paymentId: $paymentId,
            response: $data
        );

        $capturedAmount = $this->getCapturedAmount(paymentId: $paymentId)
            - $previouslyCaptured;

        $this->logSuccess(
            paymentId: $paymentId,
            event: $result->isCaptured() ? Event::CAPTURED
                : Event::PARTIALLY_CAPTURED,
            extra: Price::format(value: $capturedAmount)
        );
        return $result;
    }

    /**
     * Get captured amount.
     *
     * @throws ConfigException
     */
    public function getCapturedAmount(string $paymentId): float
    {
        try {
            $payment = Repository::get(paymentId: $paymentId);
            return (float) $payment->order?->capturedAmount;
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }

        return 0.0;
    }

    /**
     *  Prepare payload.
     */
    private function getPayload(
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): array {
        $payload = [];

        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }

        if ($creator) {
            $payload['creator'] = $creator;
        }

        if ($transactionId) {
            $payload['transactionId'] = $transactionId;
        }

        if ($invoiceId) {
            $payload['invoiceOptions'] = ['invoiceId' => $invoiceId];
        }

        return $payload;
    }
}
