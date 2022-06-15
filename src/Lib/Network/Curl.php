<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;
use Resursbank\Ecom\Config;
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
    private array $throwableHttpCodes = [
        ['400', '599'],
    ];
    private string $currentUserAgent = '';

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
     * @return CurlHandle
     */
    public function getCurlHandle()
    {
        return $this->curlHandle;
    }

    /**
     * Set short user-agent name for your requesting client. This string will be prepended to a longer summarized agent.
     * @param string $userAgent
     *
     * @return $this
     */
    public function setUserAgent(string $userAgent): Curl
    {
        $this->currentUserAgent = $userAgent;

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
            $this->currentUserAgent,
            sprintf('ECom2-%s', self::class),
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
     * @throws CurlException
     */
    private function initCurlHandle($url): Curl
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new CurlException('Invalid URL requested.');
        }

        // In netcurl, this section is splitted in several methods to make them easier to sort out.
        // Besides this, netCurl also supported multi-requests, which in our case will be more complex than we
        // need. Instead of splitting all sections up in smaller bits, we use this init function to set up
        // the handle instantly.

        $curlHandle = curl_init();
        $this->setCurlAuthentication($curlHandle);
        // Below is the list of the netCurl-methods. They are remarked if the implementation is skipped.
        // On finalization, such rows can be safely removed.

        // setCurlMultiHeaders - A bulk action that we don't need.
        $this->setCurlDynamicValues($curlHandle);
        // SSL should be set after dynamic values as they have higher priority for security, than the user defined data.
        $this->setCurlStaticValues($curlHandle);
        // setCurlPostData
        // setCurlRequestMethod

        // Custom headers setup is where we push data into the request-headers. This is where data like bearers,
        // user-agent, etc will land. Setting header data is done with setHeader.
        $this->setCurlCustomHeaders($curlHandle);

        $this->setOptionCurl($curlHandle, CURLOPT_URL, $url);

        return $this;
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
     * @return array
     */
    public function getAuthentication(): array
    {
        if (empty($this->authData['username']) && !empty(Config::$instance->credentials->getUserName())) {
            $this->setAuthentication(
                Config::$instance->credentials->getUserName(),
                Config::$instance->credentials->getPassword(),
            );
        }

        return $this->authData;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $static
     * @return Curl
     */
    public function setHeader(string $key, string $value, bool $static = false): Curl
    {
        $this->customPreHeaders[$key] = $value;
        if ($static) {
            $this->customPreHeadersStatic[$key] = $value;
        }

        return $this;
    }

    /**
     * @param CurlHandle $curlHandle
     * @return $this
     */
    private function setCurlDynamicValues(CurlHandle $curlHandle)
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
     * @return Curl
     */
    private function setCurlAuthentication(CurlHandle $curlHandle): Curl
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
        }

        foreach ($this->customPreHeaders as $headerKey => $headerValue) {
            $testHead = explode(":", $headerValue, 2);
            if (isset($testHead[1])) {
                $this->customHeaders[] = $headerValue;
            } elseif (!is_numeric($headerKey)) {
                $this->customHeaders[] = $headerKey . ": " . $headerValue;
            }
            unset($this->customPreHeaders[$headerKey]);
        }

        return $this;
    }

    /**
     * @param $curlHandle
     * @return $this
     * @since 6.1.0
     */
    private function setupHeaders($curlHandle)
    {
        if (count($this->customHeaders)) {
            $this->setOptionCurl($curlHandle, CURLOPT_HTTPHEADER, $this->customHeaders);
        }

        return $this;
    }

    /**
     * When curl has done its request, also prefetch the server's response header here, so that data
     * from that part can be handled. Separately. This is where we primarily get out HTTP Response codes.
     *
     * @param $curlHandle
     * @param $header
     * @return int
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
