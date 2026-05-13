<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Http;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Store\GetStoresRequest;
use Resursbank\Ecom\Module\Store\Repository;
use Resursbank\Ecom\Lib\Locale\Translator;
use Throwable;

/**
 * Basic controller functionality to collect stores based on credentials.
 */
class GetStoresController extends Controller
{
    /**
     * This function will:
     *
     * 1. Collect data from request (credentials).
     * 2. Update the existing Ecom instance with the credentials and env.
     * 3. Fetch stores from API.
     * 4. Return the stores as JSON response.
     *
     * If there is an error, an error response will be returned instead.
     *
     * @throws ConfigException
     */
    public function exec(): string
    {
        try {
            $data = $this->getRequestData();

            Config::setJwtAuth(auth: new Jwt(
                clientId: $data->clientId,
                clientSecret: $data->clientSecret
            ));

            Config::setIsProduction(
                isProduction: $data->environment !== Environment::TEST
            );

            return $this->respond(
                data: Repository::getApi()->getSelectList()
            );
        } catch (AuthException) {
            return $this->respondWithError(exception: new HttpException(
                message: Translator::translate(
                    phraseId: 'api-connection-failed-bad-credentials'
                )
            ));
        } catch (Throwable $error) {
            return $this->respondWithError(exception: new HttpException(
                message: Translator::translate(
                    phraseId: 'get-stores-could-not-fetch'
                ) . ' Error: ' . $error->getMessage()
            ));
        }
    }

    /**
     * @throws HttpException
     * @throws ConfigException
     */
    public function getRequestData(): GetStoresRequest
    {
        $result = $this->getRequestModel(model: GetStoresRequest::class);

        if (!$result instanceof GetStoresRequest) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}
