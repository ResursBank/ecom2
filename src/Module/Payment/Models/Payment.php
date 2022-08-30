<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\CoApplicant;
use Resursbank\Ecom\Module\Payment\Models\Payment\Customer;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Models\Payment\MetaData;
use Resursbank\Ecom\Module\Payment\Models\Payment\Status;

/**
 * Payment model used in the GET /payment call.
 */
class Payment extends Model
{
    /**
     * Payment data container that is also used by FindPayment. When FindPayment is active, some
     * returned fields are not guaranteed to be present; those fields are also nullable.
     * Application and countryCode is currently not showing in FindPayment, so to make
     * FindPayment compatible with the Payment model, we are temporary setting the missing fields
     * with empty defaults.
     *
     * @param string $id
     * @param string $created Stringed timestamp.
     * @param string $storeId
     * @param string $paymentMethodId
     * @param array $paymentActions
     * @param Customer $customer
     * @param Status $status
     * @param Application|null $application
     * @param Information|null $information
     * @param string|null $countryCode
     * @param MetaData|null $metaData
     * @param CoApplicant|null $coApplicant
     */
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $storeId,
        public readonly string $paymentMethodId,
        public readonly array $paymentActions,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly ?Application $application = null,
        public readonly ?Information $information = null,
        public readonly ?string $countryCode = null,
        public readonly ?MetaData $metaData = null,
        public readonly ?CoApplicant $coApplicant = null
    ) {
    }
}
