<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api\Traits;

use JsonException;
use ReflectionException;
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
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use stdClass;

/**
 * Shared methods for Payment API classes.
 */
trait Shared
{
    /**
     * Log request initialization.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function logRequest(
        string $paymentId,
        Event $event
    ): void {
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $paymentId,
            event: $event,
            user: User::ADMIN
        ));
    }

    /**
     * Log request failure.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     */
    private function logFailure(
        string $paymentId
    ): void {
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $paymentId,
            event: Event::REQUEST_FAILED,
            user: User::ADMIN,
            result: Result::ERROR,
            extra: Translator::translate(phraseId: 'event-request-failed')
        ));
    }

    /**
     * Log success to payment history.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function logSuccess(
        string $paymentId,
        Event $event,
        string $extra
    ): void {
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $paymentId,
            event: $event,
            user: User::ADMIN,
            result: Result::SUCCESS,
            extra: $extra
        ));
    }

    /**
     * Retrieve curl object for request.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     */
    private function getCurlObject(
        string $paymentId,
        string $method,
        array $payload
    ): Curl {
        $mapi = new Mapi();
        return new Curl(
            url: $mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/' . $paymentId . '/' . $method
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: empty($payload)
        );
    }

    /**
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    private function processResponse(
        string $paymentId,
        mixed $response
    ): Payment {
        $content = $response instanceof stdClass ? $response : new stdClass();

        $result = DataConverter::stdClassToType(
            object: $content,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            $this->logFailure(paymentId: $paymentId);
            throw new IllegalTypeException(message: 'Expected Payment');
        }

        return $result;
    }
}
