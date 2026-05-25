<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformationCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Throwable;

/**
 * Interaction with Annuity factor entities and related functionality.
 */
class Repository
{
    use ExceptionLog;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws Throwable
     */
    public static function getAnnuityFactors(
        string $paymentMethodId
    ): AnnuityInformationCollection {
        try {
            $cache = self::getCache(paymentMethodId: $paymentMethodId);

            $result = $cache->read();

            if (!$result instanceof AnnuityInformationCollection) {
                $result = self::getApi(
                    paymentMethodId: $paymentMethodId
                )->call();

                if (!$result instanceof AnnuityInformationCollection) {
                    throw new ApiException(message: 'Invalid API response.');
                }

                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws Throwable
     */
    public static function filterMethods(
        PaymentMethodCollection $paymentMethods
    ): PaymentMethodCollection {
        /** @var array<PaymentMethod> $arr */
        $arr = $paymentMethods->toArray();

        /** @var array<PaymentMethod> $result */
        $result = [];

        foreach ($arr as $method) {
            if ($method->type === Type::RESURS_INVOICE) {
                continue;
            }

            $factors = self::getAnnuityFactors(paymentMethodId: $method->id);

            if ($factors->count() === 0) {
                continue;
            }

            $result[] = $method;
        }

        $result = new PaymentMethodCollection(data: $result);

        return $result->filterByPropertyValue(
            property: 'priceSignagePossible',
            value: true
        );
    }

    /**
     * @throws ConfigException
     */
    public static function getCache(
        string $paymentMethodId
    ): Cache {
        $storeId = Config::getStoreId();

        return new Cache(
            key: 'payment-method-annuity' . sha1(
                string: serialize(value: compact('storeId', 'paymentMethodId'))
            ),
            model: AnnuityInformation::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws ConfigException
     */
    public static function getApi(
        string $paymentMethodId
    ): Get {
        return new Get(
            model: AnnuityInformation::class,
            route: Mapi::STORE_ROUTE . '/' . Config::getStoreId() .
                '/payment_methods/' . $paymentMethodId . '/annuity_factors',
            params: [],
            extractProperty: 'content'
        );
    }

    /**
     * Retrieve associative array of payment method IDs and names, filtering
     * out any payment methods that do not support part payment.
     *
     * This is useful for listing payment methods in a configuration interface.
     */
    public static function getAssocPaymentMethods(): array
    {
        $result = [];

        try {
           $methods = self::filterMethods(
               paymentMethods: PaymentMethodRepository::getPaymentMethods()
           );

            /** @var PaymentMethod $method */
            foreach ($methods as $method) {
                $result[$method->getId()] = $method->getName();
            }
        } catch (Throwable $error) {
            self::logException(exception: $error);
        }

        return $result;
    }

    /**
     * Resolve unique list of possible values for annuity periods across all
     * payment methods eligible for part payment.
     *
     * This is useful to validate input from configuration interfaces. In some
     * cases its necessary in order for the configuration interfaces to function
     * that they know in advance what values are available to a selection input
     * for example.
     *
     * We return the values as an associative array because in some cases we
     * might as well, or most, use it to render the select-box field, and
     * without the labels (say if we for example would just return an array of
     * possible month values) then the UI would flash (first showing the values
     * as only numbers before the correct options are loaded by our JS).
     */
    public static function getUniquePeriodsAsAssoc(): array
    {
        try {
            $methods = self::filterMethods(
                paymentMethods: PaymentMethodRepository::getPaymentMethods()
            );

            $periods = [];

            /** @var PaymentMethod $method */
            foreach ($methods as $method) {
                $factors = self::getAnnuityFactors(paymentMethodId: $method->getId());

                /** @var AnnuityInformation $factor */
                foreach ($factors as $factor) {
                    $periods[$factor->durationMonths] = $factor->paymentPlanName;
                }
            }

            return $periods;
        } catch (Throwable $error) {
            self::logException(exception: $error);

            return [];
        }
    }
}
