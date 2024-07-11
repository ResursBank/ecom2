<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Widget;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformationCollection;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Throwable;

/**
 * Render JavaScript code to fetch list of periods.
 */
class GetPeriods extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param string|null $methodElementId Required when using standard widget
     * JavaScript functions to manage elements. See template.
     * @param string|null $periodElementId Required when using standard widget
     * JavaScript functions to manage elements. See template.
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $storeId,
        public readonly ?string $methodElementId = null,
        public readonly ?string $periodElementId = null,
        public readonly bool $automatic = true
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-periods.js.phtml');
    }

    /**
     * Resolve list of periods, sectioned by payment method.
     */
    public function getJsonData(): string
    {
        try {
            return json_encode(
                value: $this->getPeriods(),
                flags: JSON_THROW_ON_ERROR
            );
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error($error);
            } catch (ConfigException) {
                // Do nothing.
            }
        }

        return "{}";
    }

    /**
     * Fetch annuity factors for each payment method and add them to the result
     * array. Each payment method defines an inner array with the annuity
     * factors for that payment method, keyed by the period.
     *
     * @return array
     */
    private function getPeriods(): array
    {
        $methods = $this->getPaymentMethods();

        if ($methods === null) {
            return [];
        }

        return $this->getAnnuityFactorsForMethods($methods);
    }

    /**
     * Resolve annuity factors from a collection of payment methods.
     */
    private function getAnnuityFactorsForMethods(
        PaymentMethodCollection $methods
    ): array {
        $result = [];

        /** @var PaymentMethod $method */
        foreach ($methods as $method) {
            $annuityFactors = $this->getAnnuityFactorsForMethod($method);

            if ($annuityFactors === null) {
                continue;
            }

            $result[$method->getId()] = $annuityFactors;
        }

        return $result;
    }

    /**
     * Resolve annuity factors for a specific payment method.
     */
    private function getAnnuityFactorsForMethod(PaymentMethod $method): ?array
    {
        $annuityFactors = $this->getAnnuityFactors($this->storeId, $method);

        if ($annuityFactors === null) {
            return null;
        }

        return $this->getAnnuityFactorsArray($annuityFactors);
    }

    /**
     * Resolve annuity factors as an array.
     */
    private function getAnnuityFactorsArray(
        AnnuityInformationCollection $annuityFactors
    ): array {
        $result = [];

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors as $annuityFactor) {
            $result[$annuityFactor->durationMonths] = $annuityFactor->paymentPlanName;
        }

        return $result;
    }

    /**
     * Resolve list of payment methods.
     */
    private function getPaymentMethods(): ?PaymentMethodCollection
    {
        try {
            return PaymentMethodRepository::getPaymentMethods(
                storeId: $this->storeId
            );
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error($error);
            } catch (ConfigException) {
                // Do nothing.
            }
        }

        return null;
    }

    /**
     * Get annuity factors for a specific payment method.
     */
    private function getAnnuityFactors(
        string $storeId,
        PaymentMethod $method
    ): ?AnnuityInformationCollection {
        try {
            $result = Repository::getAnnuityFactors(
                storeId: $storeId,
                paymentMethodId: $method->getId()
            );

            return count($result) > 0 ? $result : null;
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error($error);
            } catch (ConfigException) {
                // Do nothing.
            }
        }

        return null;
    }
}
