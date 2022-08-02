<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Api;


use JetBrains\PhpStorm\ArrayShape;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Rco\Repository;

class GetPaymentMethods
{
    private readonly array $methods;
    
    public function __construct(
        private readonly Mapi $mapi = new Mapi(),
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {

        // 2. Make CURL request.
        // 3. Apply response in $this->response
        // 4. Profit
    }

//    /**
//     * @return array
//     */
//    #[ArrayShape(shape: ['language' => 'string', 'customerType' => 'string', 'purchaseAmount' => 'float'])]
//    public function getPayload(): array
//    {
//        return [
//            'language' => $this->language,
//            'customerType' => $this->customerType,
//            'purchaseAmount' => $this->purchaseAmount,
//        ];
//    }

    /**
     * Perform API request and assign response to this object.
     *
     * @param string $store
     * @param string $methodId
     * @return void
     * @throws CurlException
     * @throws \JsonException
     * @throws AuthException
     * @throws TypeException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @todo Charset validation for $store and $methodId
     */
    public function exec(
        string $store,
        string $methodId = ''
    ): void {
        $data = null;

        $this->stringValidation->notEmpty(value: $store);

        try {
            $url = $this->mapi->getUrl(
                route: "stores/$store",
                params: ['payment_methods' => $methodId]
            );
            $curl = new Curl(
                url: $this->mapi->getUrl(
                    route: "stores/$store",
                    params: ['payment_methods' => $methodId]
                ),
                requestMethod: RequestMethod::GET,
                contentType: ContentType::URL,
                authType: AuthType::JWT,
                responseContentType: ContentType::JSON
            );
            $data = $curl->exec();
        } catch (CurlException $exception) {
            Config::$instance->logger->error(message: $exception);
            throw $exception;
        }

        die(var_dump($data));
    }
//
//    public function getResponse(): ?array
//    {
//        return $this->response;
//    }
//
//    /**
//     * @return bool
//     * @throws IllegalValueException
//     */
//    public function validateLanguage(): bool
//    {
//        $this->stringValidation->oneOf(
//            value: $this->language,
//            set: ['sv', 'no', 'da', 'fi', ''],
//        );
//
//        return true;
//    }
//
//    /**
//     * @return bool
//     * @throws IllegalValueException
//     */
//    public function validateCustomerType(): bool
//    {
//        $this->stringValidation->oneOf(
//            value: $this->customerType,
//            set: ['NATURAL', 'COMPANY', ''],
//        );
//
//        return true;
//    }
//
//
//
//    /**
//     * Gets the API URL to use
//     *
//     * @param string $orderReference
//     * @return string
//     */
//    private function getApiUrl(string $orderReference): string
//    {
//        return 'https://' . Repository::getApiHostname() . '/checkout/payments/' . $orderReference;
//    }
}
