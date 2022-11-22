<?php

namespace Resursbank\Ecom\Module\Callback;

use JsonException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\CallbackTypeException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\Enum\CallbackType;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Model;

class Repository
{
    private Controller $controller;

    /**
     * @param CallbackType $callbackType
     * @throws ApiException
     * @throws CallbackTypeException
     * @throws JsonException
     */
    public function __construct(
        public readonly CallbackType $callbackType,
    ) {
        $this->controller = new Controller();
        $this->getCallbackModel();
    }

    /**
     * @return Model
     * @throws CallbackTypeException
     * @throws HttpException
     */
    public function getCallbackModel(): Model
    {
        switch ($this->callbackType) {
            case CallbackType::MANAGEMENT:
                return $this->controller->getRequestModel(
                    model: Management::class
                );
            case CallbackType::AUTHORIZATION:
                return $this->controller->getRequestModel(
                    model: Authorization::class
                );
            default:
        }
        throw new CallbackTypeException('Not a valid callback type.');
    }
}
