<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Http;

use Exception;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Module\Customer\Models\GetAddressRequest;
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
     * @param string $storeId
     * @param GetAddressRequest $data
     * @return void
     */
    public function exec(
        string $storeId,
        GetAddressRequest $data
    ): void {
        try {
            $address = Repository::getAddress(
                storeId: $storeId,
                governmentId: $data->govId,
                customerType: $data->customerType
            );

            $this->respond(data: $address->toArray());
        } catch (Exception $e) {
            $this->respondWithError(exception: $e);
        }
    }

    /**
     * @return GetAddressRequest
     * @throws HttpException
     */
    public function getRequestData(): GetAddressRequest
    {
        $result = $this->getRequestModel(
            model: GetAddressRequest::class
        );

        if (!$result instanceof GetAddressRequest) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}
