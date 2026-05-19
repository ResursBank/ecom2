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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Application;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

/**
 * POST /payments/{payment_id}/create
 *
 * @todo Refactor ECP-358. Remove phpcs:ignore below when done.
 */
class Create
{
    /**
     * Assign properties.
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    /**
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
     * @todo When refactored, remove phpcs:ignore below and other suppressors above.
     */
    public function call(
        string $paymentMethodId,
        OrderLineCollection $orderLines,
        ?string $orderReference = null,
        ?Application $application = null,
        ?Customer $customer = null,
        ?Metadata $metadata = null,
        ?Options $options = null
    ): Payment {
        $parameters = $this->collectParameters(
            paymentMethodId: $paymentMethodId,
            orderLines: $orderLines,
            orderReference: $orderReference,
            application: $application,
            customer: $customer,
            metadata: $metadata,
            options: $options
        );

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE
            ),
            requestMethod: RequestMethod::POST,
            payload: $parameters,
            contentType: ContentType::JSON,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        if (!$data instanceof stdClass) {
            throw new ApiException(
                message: 'Invalid response from API. Not an stdClass.',
                code: 500
            );
        }

        $result = DataConverter::stdClassToType(
            object: $data,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalValueException(
                message: 'Response is not an instance of ' . Payment::class
            );
        }

        return $result;
    }

    /**
     * Collect parameters for call.
     *
     * @throws ConfigException
     */
    private function collectParameters(
        string $paymentMethodId,
        OrderLineCollection $orderLines,
        ?string $orderReference = null,
        ?Application $application = null,
        ?Customer $customer = null,
        ?Metadata $metadata = null,
        ?Options $options = null
    ): array {
        $parameters = [
            'storeId' => Config::getStoreId(),
            'paymentMethodId' => $paymentMethodId,
            'order' => [
                'orderLines' => $orderLines->toArray(),
            ],
        ];

        if ($orderReference) {
            $parameters['order']['orderReference'] = $orderReference;
        }

        if ($application) {
            $parameters['application'] = $application;
        }

        if ($customer) {
            $parameters['customer'] = $this->collectCustomer(
                customer: $customer
            );
        }

        if ($metadata) {
            $parameters['metadata'] = $this->collectMetadata(
                metadata: $metadata
            );
        }

        if ($options) {
            $parameters['options'] = $options;
        }

        return $parameters;
    }

    /**
     * Collect customer info.
     */
    private function collectCustomer(?Customer $customer = null): ?Customer
    {
        if (!$customer) {
            return null;
        }

        // If governmentId is empty or null, remove it from the payload.
        // Some payment methods require this field to be removed, if empty.
        if (empty($customer->governmentId)) {
            unset($customer->governmentId);
        }

        return $customer;
    }

    /**
     * Collect metadata.
     */
    private function collectMetadata(Metadata $metadata): stdClass
    {
        $result = new stdClass();

        $result->externalCustomerId = $metadata->externalCustomerId;
        $result->externalInvoiceReference = $metadata->externalInvoiceReference;

        if (isset($metadata->custom)) {
            $result->custom = $metadata->custom->toArray();
        }

        return $result;
    }
}
