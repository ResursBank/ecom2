<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Models\StoreCollection;
use stdClass;

use function get_class;
use function is_array;

/**
 * API call to get stores.
 */
class GetStores
{
    /**
     * @param Mapi $mapi
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    /**
     * Perform API request and assign response to this object.
     *
     * @param int $size | Defaults to 999999 to get all stores.
     * @param int|null $page
     * @param array $sort
     * @return StoreCollection
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws ValidationException
     * @throws JsonException
     * @throws ReflectionException
     * @todo Implement $sort and related tests after we have confirm of its structure.
     */
    public function call(
        int $size = 999999,
        ?int $page = null,
        array $sort = []
    ): StoreCollection {
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::COMMON_ROUTE . '/stores'
            ),
            requestMethod: RequestMethod::GET,
            payload: compact('page', 'size', 'sort'),
            contentType: ContentType::URL,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $body = $curl->exec()->body;

        // @todo We could validate this better. If the body is not what we expect we may want to throw and exception.
        $content = (
            $body instanceof stdClass &&
            isset($body->content) &&
            is_array(value: $body->content)
        ) ? $body->content : [];

        $result = DataConverter::arrayToCollection(
            data: $content,
            targetType: Store::class
        );

        if (!$result instanceof StoreCollection) {
            throw new IllegalTypeException(message: 'Expected StoreCollection.');
        }

        return $result;
    }
}
