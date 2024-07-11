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
    /**
     * @var string
     */
    public readonly string $content;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly ?string $storeId = null,
        public readonly ?string $paymentMethodElementId = null,
        public readonly ?string $periodElementId = null,
        public readonly bool $automatic = true
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-periods.js.phtml');
    }

    /**
     * Resolve list of periods, sectioned by payment method.
     *
     * @return string
     */
    public function getData(): string
    {
        $result = [];
        $methods = $this->getPaymentMethods();

        if ($methods === null) {
            return "{}";
        }

        // Fetch annuity factors for each payment method and add them to the
        // result array. Each payment method defines an inner array with the
        // annuity factors for that payment method, keyed by the period.
        /** @var PaymentMethod $paymentMethod */
        foreach ($this->getPaymentMethods() as $paymentMethod) {
            // Get annuity factors for the current payment method.
            $annuityFactors = $this->getAnnuityFactors(
                storeId: $this->storeId,
                method: $paymentMethod
            );

            // Skip if no annuity factors were found.
            if ($annuityFactors === null) {
                continue;
            }

            // Add annuity factors to the result array.
            $result[$paymentMethod->getId()] = [];

            /** @var AnnuityInformation $annuityFactor */
            foreach ($annuityFactors as $annuityFactor) {
                $result[$paymentMethod->getId()]
                    [$annuityFactor->durationMonths] = $annuityFactor->paymentPlanName;
            }
        }

        try {
            return json_encode(value: $result);
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
     * @return PaymentMethodCollection|null
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
     *
     * @param string $storeId
     * @param PaymentMethod $method
     * @return AnnuityInformationCollection|null
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
