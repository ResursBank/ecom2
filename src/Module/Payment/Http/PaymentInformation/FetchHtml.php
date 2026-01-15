<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Http\PaymentInformation;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Widget\PaymentInformation\Html;
use Throwable;

/**
 * Fetch payment information widget HTML via AJAX.
 */
class FetchHtml extends Controller
{
    /**
     * @throws ConfigException
     */
    public function exec(): string
    {
        try {
            $paymentId = $this->getRequestParameter(parameter: 'payment_id');

            if (!$paymentId || !(new StringValidation())->isUuid(value: $paymentId)) {
                throw new HttpException(message: 'Invalid payment id.');
            }

            return $this->respond(data: [
                'html' => (new Html(paymentId: $paymentId))->content,
            ]);
        } catch (HttpException $error) {
            return $this->respondWithError(exception: $error);
        } catch (Throwable $error) {
            $this->log(exception: $error);
            return $this->respondWithError(
                exception: new HttpException(message: 'Failed to render payment information widget.')
            );
        }
    }
}