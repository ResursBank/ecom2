<?php

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;

/**
 * Slimmed curl class for Resurs rest API's.
 */
class Curl
{
    // WrapperConfig - Replace with internals.

    // __construct should not be necessary with the current purpose of this class. In its former state
    // it has only been used to prepare for requests in a backward compatibility sort of way and should
    // not be necessary to use.

    /**
     * cURL simple handle. For this release, where we go for PHP 8, this is no longer a resource but a CurlHandle.
     * Only older PHP versions use resources.
     * @var CurlHandle
     */
    private $curlHandle;

    /**
     * The internal $curlResponse is a single response class generated from curl. From PHP 8.0 it is defined as
     * CurlHandle. Curl-responses gets its handle from curl_exec.
     * @var CurlHandle
     */
    private $curlResponse;

    /**
     * Initial holder for HTTP Head response codes.
     * CURLINFO_RESPONSE_CODE is available from 5.5
     * @var int
     */
    private $curlHttpCode = 0;

    /**
     * @var array
     */
    private $curlMultiHttpCode = [];

    /**
     * @var array
     * @since 6.1.0
     */
    private $curlResponseHeaders = [];

    /**
     * @var bool
     * @since 6.1.0
     */
    private $isCurlMulti = false;

    /**
     * @var CurlHandle cURL multi handle
     * @since 6.1.0
     */
    private $curlMultiHandle;

    /**
     * @var array $curlMultiErrors Curl Multi Request Errors.
     * @since 6.1.0
     */
    private $curlMultiErrors;

    /**
     * @var bool
     * @since 6.1.0
     */
    private $instantCurlMultiErrors = false;

    /**
     * @var array
     * @since 6.1.0
     */
    private $curlMultiHandleObjects = [];

    /**
     * @var array
     * @since 6.1.4
     */
    private $curlMultiHandleUrls = [];

    /**
     * @var array
     * @since 6.1.0
     */
    private $curlMultiResponse;

    /**
     * @var array
     * @since 6.1.0
     */
    private $customHeaders = [];

    /**
     * @var string Custom content type.
     * @since 6.1.0
     */
    private $contentType = '';

    /**
     * @var array
     * @since 6.1.4
     */
    private $defaultOptions = [];

    public function get() {
        return true;
    }
}
