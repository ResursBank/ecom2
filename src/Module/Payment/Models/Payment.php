<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Models;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Models\Payment\Application;
use Resursbank\Ecom\Module\Payment\Models\Payment\Customer;
use Resursbank\Ecom\Module\Payment\Models\Payment\Information;
use Resursbank\Ecom\Module\Payment\Models\Payment\PaymentActions;
use Resursbank\Ecom\Module\Payment\Models\Payment\Status;

/**
 * Payment model used in the GET /payment call.
 */
class Payment extends Model
{
    /**
     * @param string $id
     * @param string $created Timestamp.
     * @param string $storeId
     * @param string $paymentMethodId
     * @param array $paymentActions
     * @param Customer $customer
     * @param Status $status
     * @param Application|null $application
     * @param Information|null $information
     * @param string|null $countryCode
     * @param StringValidation $stringValidation
     * @todo Application and countryCode is currently not showing in FindPayment, so to make
     * @todo FindPayment compatible with the Payment model, we are temporary setting the missing fields
     * @todo with empty defaults.
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
        private readonly StringValidation $stringValidation = new StringValidation(),
    ) {
    }
}
