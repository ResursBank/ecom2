<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Api\GetPaymentMethods;

use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\RequestInterface;

/**
 * Defines an API request to collect a list of payment methods.
 */
class Request implements RequestInterface
{
    /**
     * @return Response
     * @throws ValidationException
     */
    public function execute(): Response
    {
        // @todo Perform actual API request, submitting the result to $response.
        return new Response(data: []);
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return true;
    }
}
