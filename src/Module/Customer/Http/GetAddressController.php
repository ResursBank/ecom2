<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Http;

use Exception;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Customer\Repository;

/**
 * Base controller class to handle operations associated with address fetching.
 *
 * NOTE: Since this library can/should not be directly exposed the intention
 * is you extend this class from your implementation and use that as the
 * endpoint for the Get Address Widget and similar integrations.
 */
class GetAddressController extends Controller
{
    /**
     * Name of government ID field.
     */
    public const PARAM_GOV_ID = 'govId';

    /**
     * Name of customer type field.
     */
    public const PARAM_CUSTOMER_TYPE = 'customerType';

    /**
     * @param StringValidation $stringValidation
     */
    public function __construct(
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * @param string $storeId
     * @param string $govId
     * @param string $customerType
     * @return void
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function exec(
        string $storeId,
        string $govId,
        string $customerType
    ): void {
        $type = CustomerType::from(value: $customerType);

        try {
            if ($type === CustomerType::NATURAL) {
                $this->stringValidation->isSwedishSsn(value: $govId);
            } else {
                $this->stringValidation->isSwedishOrg(value: $govId);
            }

            $address = Repository::getAddress(
                storeId: $storeId,
                governmentId: $govId,
                customerType: $type
            );

            $this->respond(data: $address->toArray());
        } catch (Exception $e) {
            $this->log(exception: $e);
            $this->respond(
                code: 400,
                data: ['error' => $this->getErrorMessage(exception: $e)]
            );
        }
    }
}
