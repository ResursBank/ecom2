<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Api;

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

class GetStores
{
    private readonly array $methods;
    
    public function __construct(
        private readonly Mapi $mapi = new Mapi(),
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
    }

    /**
     * Perform API request and assign response to this object.
     *
     * @return void
     * @throws CurlException
     * @throws AuthException
     * @throws TypeException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @todo Charset validation for $store and $methodId
     */
    public function exec(
        float|null $amount = null,
    ): void {
        $data = null;
        
        $url = $this->mapi->getUrl(
            route: 'stores',
            params: ['amount' => $amount]
        );
        die(var_dump($url));
        
        try {
            $curl = new Curl(
                url: $this->mapi->getUrl(
                    route: 'stores',
                    params: ['amount' => $amount]
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
}
