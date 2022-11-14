<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Http;

use Exception;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Customer\Repository;
use Resursbank\Ecom\Module\Store\Enum\Country;

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
     * Name of government id field.
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
     * @param Country $country
     * @return void
     */
    public function exec(
        string $storeId,
        Country $country
    ): void {
        try {
            $customerType = CustomerType::from(
                value: $this->getPostParam(param: self::PARAM_CUSTOMER_TYPE)
            );

            $address = Repository::getAddress(
                storeId: $storeId,
                governmentId: $this->getGovId(
                    country: $country,
                    customerType: $customerType
                ),
                customerType: $customerType
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

    /**
     * Resolve government ID from
     *
     * @param Country $country
     * @param CustomerType $customerType
     * @return string
     * @throws HttpException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getGovId(
        Country $country,
        CustomerType $customerType
    ): string {
        $result = $this->getPostParam(param: self::PARAM_GOV_ID);

        switch ($country) {
            case Country::SE:
                if ($customerType === CustomerType::NATURAL) {
                    $this->stringValidation->isSwedishSsn(value: $result);
                } else {
                    $this->stringValidation->isSwedishOrg(value: $result);
                }
                break;
            case Country::NO:
                $this->stringValidation->isNorwegianPhone(value: $result);
                break;
            default:
                break;
        }

        return $result;
    }
}
