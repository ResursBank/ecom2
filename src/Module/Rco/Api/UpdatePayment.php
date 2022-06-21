<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Api;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Request;
use Resursbank\Ecom\Module\Rco\Models\UpdatePayment\Response;

class UpdatePayment
{
    public function __construct(private readonly Config $config)
    {
    }

    public function call(Request $request, string $orderReference): Response
    {
        $curl = new Curl();
        try {
            $response = $curl->put(
                url: $this->getApiUrl(orderReference: $orderReference),
                data: $request->toArray()
            );
        } catch (CurlException $exception) {
            $this->config->logger->error(message: $exception);
        }

        return DataConverter::stdClassToType(
            object: $response,
            type: Response::class
        );
    }

    private function getApiUrl(string $orderReference): string
    {
        return $this->getApiHostname(). '/payments/' . $orderReference;
    }

    private function getApiHostname(): string
    {
        // @todo Check if we're in production or test and return appropriate hostname
        return '';
    }
}