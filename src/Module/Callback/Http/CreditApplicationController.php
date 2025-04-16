<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback\Http;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Callback\CreditApplication;

/**
 * Authorization callback controller.
 */
class CreditApplicationController extends Controller
{
    /**
     * @throws HttpException
     * @throws ConfigException
     */
    public function getRequestData(): CreditApplication
    {
        $result = $this->getRequestModel(model: CreditApplication::class);

        if (!$result instanceof CreditApplication) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}
