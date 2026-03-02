<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment;

use Exception;
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
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Application;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\Payment\TaskStatusDetails;
use Resursbank\Ecom\Lib\Model\PaymentCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get as MapiGet;
use Resursbank\Ecom\Lib\Utilities\Generic;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Api\Cancel;
use Resursbank\Ecom\Module\Payment\Api\Capture;
use Resursbank\Ecom\Module\Payment\Api\Create;
use Resursbank\Ecom\Module\Payment\Api\Get;
use Resursbank\Ecom\Module\Payment\Api\Metadata\Put;
use Resursbank\Ecom\Module\Payment\Api\Order\ActionLog\OrderLines\Add;
use Resursbank\Ecom\Module\Payment\Api\Refund;
use Resursbank\Ecom\Module\Payment\Api\Search;
use Throwable;

/**
 * Payment repository.
 *
 * Note about order lines submitted to the API: The API will not allow actions
 * such as capturing or canceling a greater sum than what is available for the
 * action on the payment but action order lines do not have to perfectly
 * match the order lines from the payment creation. As an example, it is
 * possible to have multiple order lines on a payment at creation and to then
 * capture the entire sum of the payment with a single order line in a capture
 * call.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @noinspection EfferentObjectCouplingInspection
 */
class Repository
{
    use ExceptionLog;

    /**
     * Search payments.
     *
     * If no orderReference or governmentId is supplied then the API behavior
     * is to return an unfiltered list of payments.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     */
    public static function search(
        ?string $orderReference = null,
        ?string $governmentId = null,
        ?string $storeId = null
    ): PaymentCollection {
        return (new Search())->call(
            orderReference: $orderReference,
            governmentId: $governmentId,
            storeId: $storeId
        );
    }

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
     */
    public static function get(
        string $paymentId
    ): Payment {
        $api = new Get();

        try {
            return $api->call(paymentId: $paymentId);
        } catch (Throwable $e) {
            self::logException(exception: $e);
            throw $e;
        }
    }

    /**
     * Create payment
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
     * @noinspection PhpTooManyParametersInspection
     */
    public static function create(
        string $paymentMethodId,
        OrderLineCollection $orderLines,
        ?string $orderReference = null,
        ?Application $application = null,
        ?Customer $customer = null,
        ?Metadata $metadata = null,
        ?Options $options = null
    ): Payment {
        return (new Create())->call(
            paymentMethodId: $paymentMethodId,
            orderLines: $orderLines,
            orderReference: $orderReference,
            application: $application,
            customer: $customer,
            metadata: $metadata,
            options: $options
        );
    }

    /**
     * Capture payment
     *
     * The transactionId parameter will, if supplied, appear in the accounting
     * file that Resurs Bank submits to your organization.
     *
     * @param string $paymentId Resurs payment ID.
     * @param OrderLineCollection|null $orderLines Order line collection.
     * @param string|null $creator Reference to person who performed action.
     * @param string|null $transactionId Alternative transaction identifier.
     * @param string|null $invoiceId Reference to local invoice.
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     */
    public static function capture(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): Payment {
        return (new Capture())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId,
            invoiceId: $invoiceId
        );
    }

    /**
     * Cancel payment.
     *
     * @param string $paymentId Resurs payment ID
     * @param OrderLineCollection|null $orderLines Order line collection.
     * @param string|null $creator Reference to person who performed action.
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ValidationException
     */
    public static function cancel(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null
    ): Payment {
        return (new Cancel())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator
        );
    }

    /**
     * Refund payment.
     *
     * The transactionId parameter will, if supplied, appear in the accounting
     * file that Resurs Bank submits to your organization.
     *
     * @param string $paymentId Resurs payment ID
     * @param OrderLineCollection|null $orderLines Order line collection.
     * @param string|null $creator Reference to person who performed action.
     * @param string|null $transactionId Alternative transaction identifier.
     * @param string|null $refundNoteId Reference to local credit note.
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     */
    public static function refund(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $refundNoteId = null
    ): Payment {
        return (new Refund())->call(
            paymentId: $paymentId,
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId,
            refundNoteId: $refundNoteId
        );
    }

    /**
     * Set Metadata on payment
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
     */
    public static function setMetadata(
        string $paymentId,
        Metadata $metadata
    ): Metadata {
        return (new Put())->call(paymentId: $paymentId, metadata: $metadata);
    }

    /**
     * Get client information metadata.
     *
     * @throws IllegalTypeException
     * @throws Exception
     */
    public static function getIntegrationInfoMetadata(
        string $platform,
        string $platformVersion,
        string $pluginVersion
    ): Metadata {
        $generic = new Generic();
        return new Metadata(
            custom: new Metadata\EntryCollection(data: [
                new Metadata\Entry(
                    key: 'resurs_platform',
                    value: $platform
                ),
                new Metadata\Entry(
                    key: 'resurs_platform_version',
                    value: $platformVersion
                ),
                new Metadata\Entry(
                    key: 'resurs_platform_plugin_version',
                    value: $pluginVersion
                ),
                new Metadata\Entry(
                    key: 'resurs_platform_php_version',
                    value: PHP_VERSION
                ),
                new Metadata\Entry(
                    key: 'resurs_platform_ecom2_version',
                    value: $generic->getVersionByComposer(
                        location: __DIR__,
                        maxDepth: 4
                    )
                )
            ])
        );
    }

    /**
     * Add new order lines to payment.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     */
    public static function addOrderLines(
        string $paymentId,
        OrderLineCollection $orderLines
    ): Payment {
        return (new Add())->call(
            paymentId: $paymentId,
            orderLines: $orderLines
        );
    }

    /**
     * Fetch TaskStatusDetails object relating to our payment from API.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     */
    public static function getTaskStatusDetails(
        string $paymentId
    ): TaskStatusDetails {
        self::validatePaymentId(paymentId: $paymentId);

        $result = (new MapiGet(
            model: TaskStatusDetails::class,
            route: Mapi::PAYMENT_ROUTE . "/$paymentId/tasks/status",
            params: []
        ))->call();

        if (!$result instanceof TaskStatusDetails) {
            throw new ApiException(message: 'Invalid API response.');
        }

        return $result;
    }

    /**
     * @throws IllegalValueException
     */
    private static function validatePaymentId(
        string $paymentId
    ): void {
        (new StringValidation())->isUuid(value: $paymentId);
    }
}
