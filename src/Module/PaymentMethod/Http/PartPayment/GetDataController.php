<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Http\PartPayment;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\PaymentMethod\PartPayment\InfoResponse;
use Resursbank\Ecom\Module\UserSettings\Repository;
use Resursbank\Ecom\Module\Widget\PartPayment\Html as PartPaymentWidget;
use Resursbank\Ecom\Module\Widget\ReadMore\Html as ReadMore;
use Throwable;

/**
 * Basic controller function to render updated information for part payment
 * widget via AJAX.
 */
class GetDataController extends Controller
{
    /**
     * @throws ConfigException
     */
    public function exec(): string
    {
        try {
            $amount = (float) $this->getRequestParameter(parameter: 'amount');

            if ($amount <= 0) {
                throw new HttpException(message: 'Invalid amount');
            }

            $paymentMethod = Repository::getPartPaymentMethod();

            if ($paymentMethod == null) {
                throw new HttpException(message: 'Part payment method is not configured');
            }

            $widget = new PartPaymentWidget(
                amount: $amount
            );

            $readMoreWidget = new ReadMore(
                paymentMethod: $paymentMethod,
                amount: $amount
            );

            $response = new InfoResponse(
                startingAt: $widget->cost->monthlyCost,
                html: $widget->content,
                readMoreHtml: $readMoreWidget->content,
            );

            return $this->respond(data: $response->toArray());
        } catch (HttpException $error) {
            return $this->respondWithError(exception: $error);
        } catch (Throwable $error) {
            $this->log(exception: $error);
            return $this->respondWithError(
                exception: new HttpException(message: 'Failed to render part payment widget components.')
            );
        }
    }
}
