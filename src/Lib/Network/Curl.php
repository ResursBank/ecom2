<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;
use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;

/**
 * Slimmed curl class for Resurs REST API's.
 */
class Curl
{
    // WrapperConfig - Replace with internals.

    // __construct should not be necessary with the current purpose of this class. In its former state
    // it has only been used to prepare for requests in a backward compatibility sort of way and should
    // not be necessary to use.

    /**
     * Internal response container. Binary safe (ref: curl).
     * @var mixed
     */
    private mixed $curlResponse;

    /**
     * Initial holder for HTTP Head response codes.
     * CURLINFO_RESPONSE_CODE is available from 5.5
     * @var int
     */
    private int $curlHttpCode = 0;

    /**
     * Custom headers, before pushing data into the CurlHandle. Usually 'X-HEADER-KEY'-like data but
     * also Content-Type and Content-Length will be prepared and store until everything is executed.
     * This is not where User-Agent are stored.
     *
     * @var array
     * @since 6.1.0
     */
    private array $customPreHeaders = [];

    /**
     * Same as custom headers but static that makes sure they are stored after a request so that they can be re-used.
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
     * Default options for curl. Observe that we always set FOLLOWLOCATION to false since there may be
     * sites configured to prohibit redirect-links.
     * @var array
     */
    private array $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_ENCODING => 1,
        CURLOPT_USERAGENT => '',
        CURLOPT_SSLVERSION => CURL_SSLVERSION_DEFAULT,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => ['Accept-Language: en'],
    ];

    /**
     * @var array Authentication data (for curlauth/basic only)..
     */
    private array $authData = ['username' => '', 'password' => '', 'type' => 1];

    /**
     * Default list of http codes that comes from the HTTP head response which are throwable.
     * Defined as a [fromcode, tocode] list.
     * @var array
     */
    private array $throwableHttpCodes = [
        ['400', '599'],
    ];

    /**
     * Temporary feature to prepare user auth data (not MAPI) manually.
     *
     * @return array
     */
    public function getAuthentication(): array
    {
        if (empty($this->authData['username']) && !empty(Config::$instance->credentials->username)) {
            $this->setAuthentication(
                Config::$instance->credentials->username,
                Config::$instance->credentials->password,
            );
        }

        return $this->authData;
    }

    /**
     * Curl based authentication (not token bearers).
     *
     * @param string $username
     * @param string $password
     * @param int $authType
     * @return $this
     */
    public function setAuthentication(
        string $username,
        string $password,
        int $authType = CURLAUTH_BASIC,
    ): Curl {
        $this->authData['username'] = $username;
        $this->authData['password'] = $password;
        $this->authData['type'] = $authType;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $static
     * @return Curl
     */
    public function setHeader(string $key, string $value, bool $static = false): Curl
    {
        $this->customPreHeaders[] = new Header(
            key: $key,
            value: $value,
            isStatic: $static
        );

        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->curlHttpCode;
    }

    /**
     * Get parsed response. No longer using IO.
     *
     * @return mixed
     * @throws JsonException
     */
    public function getParsed(): mixed
    {
        return preg_match(
            pattern: '/\/json/i',
            subject: $this->getHeader('content-type')
        ) ? json_decode(
            json: $this->getBody(),
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        ) : $this->getBody();
    }

    /**
     * Get specific response header data by its keyname.
     * @param string $specificKey
     * @return string
     */
    public function getHeader(string $specificKey = ''): string
    {
        $return = [];

        foreach ($this->curlResponseHeaders as $headKey => $headArray) {
            // Something has pushed in duplicates of a header row, so lets pop one.
            if (is_array($headArray) && count($headArray) > 1) {
                $headArray = array_pop($headArray);
            }
            if (is_array($headArray) && count($headArray) === 1) {
                if (!$specificKey) {
                    $return[] = sprintf("%s: %s", $headKey, array_pop($headArray));
                } elseif (strtolower($specificKey) === strtolower($headKey)) {
                    $return[] = sprintf("%s", array_pop($headArray));
                } elseif (strtolower($specificKey) === 'http') {
                    if (0 === stripos($headKey, "http")) {
                        $return[] = sprintf("%s", array_pop($headArray));
                    }
                }
            }
        }

        return implode("\n", $return);
    }

    /**
     * Return raw body from response. Binary safe (Thanks to curl).
     *
     * @return mixed
     */
    public function getBody(): mixed
    {
        return $this->curlResponse;
    }

    /**
     * @param string $url
     * @param array $data
     * @param DataType $dataType
     * @return $this
     * @throws CurlException
     * @throws Exception
     */
    public function get(string $url, array $data = [], $dataType = DataType::JSON)
    {
        return $this->request($url, $data, RequestMethod::GET, $dataType);
    }

    /**
     * @param string $url
     * @param array $data
     * @param int $method
     * @param DataType $dataType
     * @return $this
     * @throws CurlException
     * @throws Exception
     * @see https://developer.mozilla.org/en-US/docsfu/Web/HTTP/Methods
     */
    private function request(
        string $url,
        array $data,
        $method = RequestMethod::GET,
        $dataType = DataType::JSON
    ): Curl {
        $this->resetCurlRequest();
        $this->getCurlRequest(
            $this->initCurlHandle($url, $data, $method, $dataType)
        );

        return $this;
    }

    /**
     * Reset curl on each new curlrequest to make sure old responses is no longer present.
     */
    private function resetCurlRequest(): Curl
    {
        $this->customHeaders = [];
        $this->curlResponseHeaders = [];

        return $this;
    }

    /**
     * @param CurlHandle $curlHandle
     * @return $this
     * @throws CurlException
     * @throws Exception
     */
    private function getCurlRequest(CurlHandle $curlHandle): Curl
    {
        $this->curlResponse = curl_exec($curlHandle);
        // Friendly anti-backfire support.
        $this->curlHttpCode = curl_getinfo(
            $curlHandle,
            CURLINFO_RESPONSE_CODE
        );
        $this->getCurlException($curlHandle, $this->curlHttpCode);

        return $this;
    }

    /**
     * @param CurlHandle $curlHandle
     * @param int $httpCode
     * @return Curl
     * @throws CurlException
     */
    private function getCurlException(CurlHandle $curlHandle, int $httpCode): Curl
    {
        $errorString = curl_error($curlHandle);
        $errorCode = curl_errno($curlHandle);
        if ($errorCode) {
            throw new CurlException(
                sprintf(
                    'curl error (%s): %s',
                    $errorCode,
                    $errorString
                ),
                $errorCode
            );
        }

        $httpHead = $this->getHeader('http');
        if (empty($errorString) && !empty($httpHead)) {
            $errorString = $httpHead;
        }
        $this->getHttpException($errorString, $httpCode);

        return $this;
    }

    /**
     * Throw on any code that matches the store throwableHttpCode (use with setThrowableHttpCodes())
     *
     * @param string $httpMessageString
     * @param int $httpCode
     * @throws CurlException
     */
    public function getHttpException(
        string $httpMessageString = '',
        int $httpCode = 0
    ): void {
        if (!is_array($this->throwableHttpCodes)) {
            $this->throwableHttpCodes = [];
        }
        foreach ($this->throwableHttpCodes as $codeListArray => $codeArray) {
            if ((isset($codeArray[1]) && $httpCode >= (int)$codeArray[0] && $httpCode <= (int)$codeArray[1])) {
                throw new CurlException(
                    sprintf(
                        'Error %d returned from server: "%s".',
                        $httpCode,
                        $httpMessageString
                    ),
                    $httpCode
                );
            }
        }
    }

    /**
     * @throws CurlException
     */
    private function initCurlHandle(
        string $url,
        array $data,
        $method = RequestMethod::GET,
        $dataType = DataType::JSON
    ): CurlHandle {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new CurlException('Invalid URL requested.');
        }

        // In netcurl, this section is splitted in several methods to make them easier to sort out.
        // Besides this, netCurl also supported multi-requests, which in our case will be more complex than we
        // need. Instead of splitting all sections up in smaller bits, we use this init function to set up
        // the handle instantly.

        $curlHandle = curl_init();
        // ECP-18
        $this->setCurlAuthentication($curlHandle);
        // Below is the list of the netCurl-methods. They are remarked if the implementation is skipped.
        // On finalization, such rows can be safely removed.

        $this->setCurlDynamicValues($curlHandle);
        // SSL should be set after dynamic values as they have higher priority for security, than the user defined data.
        $this->setCurlStaticValues($curlHandle);
        $this->setCurlPostData($curlHandle, $data, $method, $dataType);
        $this->setCurlRequestMethod($curlHandle, $method);

        // Custom headers setup is where we push data into the request-headers. This is where data like bearers,
        // user-agent, etc will land. Setting header data is done with setHeader.
        $this->setCurlCustomHeaders($curlHandle);
        $this->setOptionCurl($curlHandle, CURLOPT_URL, $url);

        return $curlHandle;
    }

    /**
     * @param CurlHandle $curlHandle
     * @return Curl
     */
    private function setCurlAuthentication(CurlHandle $curlHandle): Curl
    {
        /**
         * @todo ECP-18
         */
        if (!empty($this->authData['username']) && !empty($this->authData['password'])) {
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

    /**
     * @param CurlHandle $curlHandle
     * @param int $key
     * @param mixed $value
     * @return bool
     */
    public function setOptionCurl(CurlHandle $curlHandle, int $key, mixed $value): bool
    {
        return curl_setopt($curlHandle, $key, $value);
    }

    /**
     * @param CurlHandle $curlHandle
     * @return $this
     */
    private function setCurlDynamicValues(CurlHandle $curlHandle): Curl
    {
        foreach ($this->options as $curlKey => $curlValue) {
            $this->setOptionCurl($curlHandle, $curlKey, $curlValue);
        }

        return $this;
    }

    /**
     * Values intended to not be overridden by user input.
     *
     * @param mixed $curlHandle
     * @return $this
     * @since 6.1.0
     */
    private function setCurlStaticValues(CurlHandle $curlHandle): Curl
    {
        $this->setOptionCurl($curlHandle, CURLOPT_RETURNTRANSFER, true);
        $this->setOptionCurl($curlHandle, CURLOPT_HEADER, false);
        $this->setOptionCurl($curlHandle, CURLOPT_AUTOREFERER, true);
        $this->setOptionCurl($curlHandle, CURLINFO_HEADER_OUT, true);
        $this->setOptionCurl($curlHandle, CURLOPT_HEADERFUNCTION, [$this, 'getCurlHeaderRow']);
        $this->setOptionCurl($curlHandle, CURLOPT_USERAGENT, $this->getUserAgent());

        return $this;
    }

    /**
     * Final user agent string that will be pushed into http-requests.
     * @return string
     */
    public function getUserAgent(): string
    {
        $return = [];

        $userAgentArray = [
            Config::$instance->userAgent,
            sprintf('ECom2-%s', $this->getNameSpaceClass(self::class)),
            sprintf('PHP-%s', PHP_VERSION),
        ];

        foreach ($userAgentArray as $item) {
            if (!empty($item)) {
                $return[] = $item;
            }
        }

        return implode(' +', $return);
    }

    /**
     * @param $class
     * @return mixed|string
     */
    private function getNameSpaceClass(string $class)
    {
        $return = '';

        $wrapperClassExplode = explode('\\', $class);
        if (is_array($wrapperClassExplode) && count($wrapperClassExplode)) {
            $return = $wrapperClassExplode[count($wrapperClassExplode) - 1];
        }

        return $return;
    }

    /**
     * @param CurlHandle $curlHandle
     * @param array $requestData
     * @param $requestMethod
     * @param $dataType
     * @return $this
     */
    private function setCurlPostData(
        CurlHandle $curlHandle,
        array $requestData,
        $requestMethod,
        $dataType
    ): Curl {
        $stringifyData = $this->getRequestData($requestData, $requestMethod, $dataType);

        // In the main netcurl library a switch-case was used as it also supported XML content. This slimmed
        // section is intended to just support JSON and regular get-post-data.

        if ($dataType === DataType::JSON) {
            $jsonContentType = 'application/json; charset=utf-8';
            $this->customPreHeaders[] = new Header(
                key: 'Content-Type',
                value: $jsonContentType
            );
            $this->customPreHeaders[] = new Header(
                key: 'Content-Length',
                value: strlen($stringifyData)
            );
            $this->setOptionCurl($curlHandle, CURLOPT_POSTFIELDS, $stringifyData);
        } else {
            if ($requestMethod === RequestMethod::POST) {
                $this->setOptionCurl($curlHandle, CURLOPT_POST, true);
            }
            $this->setOptionCurl($curlHandle, CURLOPT_POSTFIELDS, $stringifyData);
        }

        return $this;
    }

    /**
     * @param mixed $requestData
     * @param $requestMethod
     * @param $dataType
     * @return string
     */
    private function getRequestData(mixed $requestData, $requestMethod, $dataType): string
    {
        $return = '';

        // In the main netcurl library a switch-case was used as it also supported XML content. This slimmed
        // section is intended to just support JSON and regular get-post-data.

        if ($dataType === DataType::JSON) {
            $return = $this->getJsonData($requestData);
        } else {
            $requestQuery = '';

            if ($requestMethod === RequestMethod::GET) {
                $requestQuery = '&';
            }
            if ($this->hasData($requestData)) {
                $httpQuery = http_build_query($requestData);
                if (!empty($httpQuery)) {
                    $return = $requestQuery . $httpQuery;
                }
            }
        }

        return $return;
    }

    /**
     * Handle json properly.
     *
     * @param $transformData
     * @return string
     */
    private function getJsonData($transformData): string
    {
        $return = $transformData;

        if (is_string($transformData)) {
            $stringTest = json_decode($transformData, false);
            if (is_object($stringTest) || is_array($stringTest)) {
                $return = $transformData;
            }
        } else {
            $return = json_encode($transformData);
        }

        return (string)$return;
    }

    /**
     * @param $arrayObject
     * @return bool
     */
    public function hasData($arrayObject): bool
    {
        $return = false;

        if (is_object($arrayObject)) {
            $return = true;
        } elseif (is_array($arrayObject) && count($arrayObject)) {
            $return = true;
        }

        return $return;
    }

    /**
     * @param CurlHandle $curlHandle
     * @param $requestMethod
     * @return $this
     */
    private function setCurlRequestMethod(CurlHandle $curlHandle, $requestMethod): Curl
    {
        // Method REQUEST is removed from this section.

        switch ($requestMethod) {
            case RequestMethod::POST:
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
                break;
            case RequestMethod::DELETE:
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case RequestMethod::HEAD:
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'HEAD');
                break;
            case RequestMethod::PUT:
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case RequestMethod::PATCH:
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            default:
                // Making sure we send data in proper formatting if there is bad user configuration.
                // Bad configuration is when both GET+POST data parameters are sent as a GET when the
                // correct set up in that case is a POST.
                $this->setOptionCurl($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        return $this;
    }

    /**
     * Custom headers handling.
     *
     * @param CurlHandle $curlHandle
     * @return Curl
     */
    private function setCurlCustomHeaders(CurlHandle $curlHandle): Curl
    {
        $this->setProperCustomHeader();
        $this->setupHeaders($curlHandle);

        return $this;
    }

    /**
     * Fix problematic header data by converting them to proper outputs.
     *
     * @return $this
     * @since 6.1.0
     */
    private function setProperCustomHeader(): Curl
    {
        // Merge static header data into customPreHeaders.
        foreach ($this->customPreHeadersStatic as $headerKey => $headerValue) {
            $this->customPreHeaders[$headerKey] = $headerValue;
            $this->customPreHeaders[] = new Header(
                key: $headerKey,
                value: $headerValue,
                isStatic: true
            );
        }

        /**
         * Non associative headers are no longer allowed.
         * @var Header $header
         */
        foreach ($this->customPreHeaders as $header) {
            // Rendering final header array to hand over to curl.
            // This is sent over to curl as is.
            $this->customHeaders[] = sprintf('%s: %s', $header->key, $header->value);
        }
        // Empty out preHeaders.
        $this->customPreHeaders = [];

        return $this;
    }

    /**
     * @param $curlHandle
     * @return $this
     */
    private function setupHeaders($curlHandle): Curl
    {
        if (count($this->customHeaders)) {
            $this->setOptionCurl($curlHandle, CURLOPT_HTTPHEADER, $this->customHeaders);
        }

        return $this;
    }

    /**
     * @param string $url
     * @param array $data
     * @param DataType $dataType
     * @return $this
     * @throws CurlException|Exception
     */
    public function post(string $url, array $data = [], $dataType = DataType::JSON): Curl
    {
        return $this->request($url, $data, RequestMethod::POST, $dataType);
    }

    /**
     * @param string $url
     * @param array $data
     * @param DataType $dataType
     * @return $this
     * @throws CurlException|Exception
     */
    public function put(string $url, array $data = [], $dataType = DataType::JSON): Curl
    {
        return $this->request($url, $data, RequestMethod::PUT, $dataType);
    }

    /**
     * @param string $url
     * @param array $data
     * @param int $dataType
     * @return $this
     * @throws CurlException|Exception
     */
    public function delete(string $url, array $data = [], $dataType = DataType::JSON): Curl
    {
        return $this->request($url, $data, RequestMethod::DELETE, $dataType);
    }

    /**
     * When curl has done its request, also prefetch the server's response header here, so that data
     * from that part can be handled. Separately. This is where we primarily get out HTTP Response codes.
     *
     * @param $curlHandle
     * @param $header
     * @return int
     * @noinspection PhpUnusedParameterInspection
     */
    private function getCurlHeaderRow($curlHandle, $header): int
    {
        $headSplit = explode(':', $header, 2);
        $spacedSplit = explode(' ', $header, 2);

        if (count($headSplit) < 2) {
            if (count($spacedSplit) > 1) {
                $this->curlResponseHeaders[$spacedSplit[0]][] = trim($spacedSplit[1]);
            }
            return strlen($header);
        }

        $this->curlResponseHeaders[$headSplit[0]][] = trim($headSplit[1]);

        return strlen($header);
    }
}
