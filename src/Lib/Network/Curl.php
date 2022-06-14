<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;
use Resursbank\Ecom\Exception\CurlException;

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
     * HTTP GET Method (default).
     * @var int
     */
    public const METHOD_GET = 0;
    /**
     * HTTP POST Method.
     * @var int
     */
    public const METHOD_POST = 1;
    /**
     * HTTP PUT Method.
     * @var int
     */
    public const METHOD_PUT = 2;
    /**
     * HTTP DELETE Method.
     * @var int
     */
    public const METHOD_DELETE = 3;
    /**
     * HTTP HEAD Method.
     * @var int
     */
    public const METHOD_HEAD = 4;
    /**
     * HTTP REQUEST Method.
     * @var int
     */
    public const METHOD_REQUEST = 5;
    /**
     * HTTP PATCH Method.
     * @var int
     */
    public const METHOD_PATCH = 6;

    /**
     * Default DataType means that we usually use the standard GET/POST variables like ?var=val&var1=val1
     * @var int
     */
    const TYPE_DEFAULT = 0;

    /**
     * Using JSON-formatted data.
     * @var int
     */
    const TYPE_JSON = 1;

    /**
     * cURL simple handle. For this release, where we go for PHP 8, this is no longer a resource but a CurlHandle.
     * Only older PHP versions use resources.
     * @var CurlHandle
     */
    private CurlHandle $curlHandle;

    /**
     * The internal $curlResponse is a single response class generated from curl. From PHP 8.0 it is defined as
     * CurlHandle. Curl-responses gets its handle from curl_exec.
     * @var CurlHandle
     */
    private CurlHandle $curlResponse;

    /**
     * Initial holder for HTTP Head response codes.
     * CURLINFO_RESPONSE_CODE is available from 5.5
     * @var int
     */
    private int $curlHttpCode = 0;

    /**
     * @var array
     * @since 6.1.0
     */
    private array $customPreHeaders = [];

    /**
     * Static headers that will not reset between each request-init.
     * @var array
     */
    private array $customPreHeadersStatic = [];

    /**
     * @var array
     */
    private array $curlResponseHeaders = [];

    /**
     * @var array
     */
    private array $customHeaders = [];

    /**
     * @var string Custom content type.
     */
    private string $contentType = '';

    /**
     * @var array
     */
    private array $defaultOptions = [];

    /**
     * @var array Authentication data (for curlauth/basic only)..
     */
    private $authData = ['username' => '', 'password' => '', 'type' => 1];

    /**
     * Default list of http codes that comes from the HTTP head response which are throwable.
     * Defined as a [fromcode, tocode] list.
     * @var array
     */
    private $throwableHttpCodes = [
        ['400', '599'],
    ];

    /**
     * Reset curl on each new curlrequest to make sure old responses is no longer present.
     */
    private function resetCurlRequest()
    {
        $this->customHeaders = [];
        $this->curlResponseHeaders = [];

        return $this;
    }

    /**
     * @throws CurlException
     */
    private function initCurlHandle($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new CurlException('Invalid URL requested.');
        }

        $curlHandle = curl_init();
        $this->setCurlAuthentication($curlHandle);

        return $this;
    }

    public function setAuthentication(
        $username,
        $password,
        $authType = CURLAUTH_BASIC,
    ) {
        $this->authData['username'] = $username;
        $this->authData['password'] = $password;
        $this->authData['type'] = $authType;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $static
     * @return self
     */
    public function setHeader(string $key, string $value, bool $static = false)
    {
        $this->customPreHeaders[$key] = $value;
        if ($static) {
            $this->customPreHeadersStatic[$key] = $value;
        }

        return $this;
    }

    /**
     * @param CurlHandle $curlHandle
     * @param int $key
     * @param mixed $value
     * @return bool
     */
    public function setOptionCurl(CurlHandle $curlHandle, int $key, mixed $value)
    {
        return curl_setopt($curlHandle, $key, $value)
    }

    /**
     * @param CurlHandle $curlHandle
     * @return Curl
     */
    private function setCurlAuthentication(CurlHandle $curlHandle)
    {
        if (!empty($this->authData['usernane']) && !empty($this->authData['password'])) {
            $this->setOptionCurl(
                $curlHandle,
                CURLOPT_HTTPAUTH,
                !$this->authData['type'] ? $this->authData['type'] : HTTP_AUTH_BASIC
            );
            $this->setOptionCurl(
                $curlHandle,
                CURLOPT_USERPWD,
                $this->authData['username']
            );
        }

        return $this;
    }

    private function request(string $url, array $data, $method = self::METHOD_GET, $dataType = self::TYPE_DEFAULT)
    {
        $this->resetCurlRequest();
        $this->initCurlHandle($url);
    }

    public function get(string $url, array $data = [], int $dataType = self::TYPE_DEFAULT)
    {
        return $this->request($url, $data, self::METHOD_GET, $dataType);
    }

    public function post(string $url, array $data = [], int $dataType = self::TYPE_DEFAULT)
    {
        return $this->request($url, $data, self::METHOD_POST, $dataType);
    }

    public function put(string $url, array $data = [], int $dataType = self::TYPE_DEFAULT)
    {
        return $this->request($url, $data, self::METHOD_PUT, $dataType);
    }

    public function delete(string $url, array $data = [], int $dataType = self::TYPE_DEFAULT)
    {
        return $this->request($url, $data, self::METHOD_DELETE, $dataType);
    }

    public function head(string $url, array $data = [], int $dataType = self::TYPE_DEFAULT)
    {
        return $this->request($url, $data, self::METHOD_HEAD, $dataType);
    }
}
