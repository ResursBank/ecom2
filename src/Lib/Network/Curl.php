<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use stdClass;
use CurlHandle;
use InvalidArgumentException;
use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Network\Model\JwtToken;
use Resursbank\Ecom\Lib\Network\Model\Response;
use Resursbank\Ecom\Lib\Network\Model\Header;
use Resursbank\Ecom\Lib\Validation\StringValidation;

use function is_string;
use function strlen;

/**
 * Curl wrapper.
 */
class Curl
{
    /**
     * @var CurlHandle
     */
    public readonly CurlHandle $ch;

    /**
     * @param string $url
     * @param RequestMethod $requestMethod
     * @param array $headers
     * @param array $payload
     * @param ContentType $contentType
     * @param AuthType $authType
     * @param ApiType $apiType
     * @param StringValidation $stringValidation
     * @param ContentType|null $responseContentType
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     * @todo $headers and associated methods should be moved to a collection model / service layer.
     */
    public function __construct(
        string $url,
        public readonly RequestMethod $requestMethod,
        array $headers = [],
        array $payload = [],
        public readonly ContentType $contentType = ContentType::JSON,
        public readonly AuthType $authType = AuthType::JWT,
        public readonly ApiType $apiType = ApiType::MERCHANT,
        private readonly StringValidation $stringValidation = new StringValidation(),
        public ?ContentType $responseContentType = null
    ) {
        if (!$this->responseContentType) {
            $this->responseContentType = $this->contentType;
        }

        // Initialize Curl.
        $ch = $this->init(url: $url, headers: $headers, payload: $payload);

        // Setup plaintext auth if requested.
        $this->setAuth(ch: $ch);

        $this->ch = $ch;
    }

    /**
     * @return Response
     * @throws CurlException
     * @throws JsonException
     * @throws IllegalTypeException
     * @throws EmptyValueException
     */
    public function exec(): Response
    {
        $body = curl_exec(handle: $this->ch);

        $this->handleError(); // We want to check for errors immediately after running curl_exec

        if (!is_string(value: $body)) {
            throw new IllegalTypeException(
                message: 'Curl response type is ' . gettype($body) . ', expected string.'
            );
        }

        $code = (int) curl_getinfo(
            handle: $this->ch,
            option: CURLINFO_RESPONSE_CODE
        );

        if ($this->responseContentType === ContentType::JSON) {
            $this->stringValidation->notEmpty(value: $body);
            /** @psalm-suppress MixedAssignment */
            $body = json_decode(
                json: $body,
                associative: false,
                flags: JSON_THROW_ON_ERROR
            );
        } elseif ($this->responseContentType === ContentType::RAW) {
            $bodyObj = new stdClass();
            $bodyObj->message = $body;
            $body = $bodyObj;
        }

        if (!($body instanceof stdClass) && !is_array(value: $body)) {
            throw new IllegalTypeException(
                message: 'Curl response body is not an object or an array.'
            );
        }

        $this->handleError();

        curl_close(handle: $this->ch);

        return new Response(body: $body, code: $code);
    }

    /**
     * @param string $url
     * @param array $payload
     * @param AuthType $authType
     * @return Response
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function get(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::GET,
            payload: $payload,
            contentType: ContentType::URL,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @param string $url
     * @param array $payload
     * @param AuthType $authType
     * @return Response
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function post(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @param string $url
     * @param AuthType $authType
     * @return Response
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function delete(
        string $url,
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::DELETE,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
     * @param string $url
     * @param array $payload
     * @param AuthType $authType
     * @param ContentType $contentType
     * @param ContentType|null $responseContentType
     * @return Response
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    public static function put(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT,
        ContentType $contentType = ContentType::JSON,
        ?ContentType $responseContentType = null
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::PUT,
            payload: $payload,
            contentType: $contentType,
            authType: $authType,
            responseContentType: $responseContentType
        );

        return $curl->exec();
    }

    /**
     * @param string $url
     * @param array $headers
     * @param array $payload
     * @return CurlHandle
     * @throws JsonException
     * @throws ValidationException
     * @todo Check if CURLOPT_ENCODING should be included and what value it should be assigned.
     */
    private function init(
        string $url,
        array $headers,
        array $payload
    ): CurlHandle {
        $ch = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FAILONERROR => false, // Don't treat HTTP code 400+ as error.
            CURLOPT_AUTOREFERER => true, // Follow redirects.
            CURLINFO_HEADER_OUT => true, // Track outgoing headers for debugging.
            CURLOPT_HEADER => false, // Do not include header in output.
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => $this->getUserAgent(),
            CURLOPT_HTTPHEADER => $this->getHeadersData(
                headers: $this->generateHeaders(
                    headers: $headers,
                    payload: $payload
                )
            ),
            CURLOPT_CUSTOMREQUEST => $this->getCustomRequestValue(),
            CURLOPT_URL => $this->generateUrl(url: $url, payload: $payload),
            CURLOPT_SSLVERSION => CURL_SSLVERSION_DEFAULT,
        ];
        if (!empty(Config::$instance->proxy)) {
            $options[CURLOPT_PROXY] = Config::$instance->proxy;
            $options[CURLOPT_PROXYTYPE] = Config::$instance->proxyType;
        }
        if ((int)Config::$instance->timeout) {
            $options[CURLOPT_CONNECTTIMEOUT] = ceil(Config::$instance->timeout) / 2;
            $options[CURLOPT_TIMEOUT] = ceil(Config::$instance->timeout);
        }

        curl_setopt_array(handle: $ch, options: $options);

        $this->setContent(ch: $ch, payload: $payload);

        return $ch;
    }

    /**
     * @return bool
     */
    public function hasBodyData(): bool
    {
        return (
            $this->requestMethod === RequestMethod::POST ||
            $this->requestMethod === RequestMethod::PUT ||
            $this->requestMethod === RequestMethod::DELETE
        );
    }

    /**
     * @param string $url
     * @param array $payload
     * @return string
     * @throws JsonException
     * @throws ValidationException
     * @todo Add URL prefix based on $this->authType?
     */
    public function generateUrl(string $url, array $payload): string
    {
        $url .= $this->hasBodyData()
            ? '' :
            '?' . $this->getPayloadData(payload: $payload);

        if (!filter_var(value: $url, filter: FILTER_VALIDATE_URL)) {
            throw new ValidationException(message: 'Invalid URL requested.');
        }

        return $url;
    }

    /**
     * @param array $headers
     * @param array $payload
     * @return array<Header>
     * @throws JsonException
     * @todo See constructor todo. If kept we should maybe change its visibility.
     */
    public function generateHeaders(array $headers, array $payload): array
    {
        foreach ($headers as $header) {
            if (!$header instanceof Header) {
                throw new InvalidArgumentException(
                    message: 'Header must be an instance of Header.'
                );
            }
        }

        if (!$this->hasHeader(headers: $headers, key: 'content-type')) {
            $headers[] = new Header(
                key: 'content-type',
                value: $this->getContentType()
            );
        }

        if (!$this->hasHeader(headers: $headers, key: 'content-length') && $this->hasBodyData()) {
            $headers[] = new Header(
                key: 'content-length',
                value: strlen(string: $this->getPayloadData(payload: $payload))
            );
        }

        if (!$this->hasHeader(headers: $headers, key: 'accept-language')) {
            $headers[] = new Header(
                key: 'accept-language',
                value: 'en'
            );
        }

        return $headers;
    }

    /**
     * @param array $headers
     * @param string $key
     * @return bool
     * @todo See constructor todo. If kept we should maybe change its visibility.
     */
    public function hasHeader(
        array $headers,
        string $key
    ): bool {
        return count($this->findHeaders(headers: $headers, key: $key)) > 0;
    }

    /**
     * Retrieve list of headers where $key matches.
     * @todo See constructor todo. If kept we should maybe change its visibility.
     *
     * @param array $headers
     * @param string $key
     * @return array
     */
    public function findHeaders(
        array $headers,
        string $key
    ): array {
        $key = strtolower(string: $key);

        return array_filter(
            array: $headers,
            callback: static function ($header) use ($key) {
                return strtolower(string: $header->key) === $key;
            }
        );
    }

    /**
     * @param array $headers
     * @return array
     */
    public function getHeadersData(
        array $headers
    ): array {
        $result = [];

        foreach ($headers as $header) {
            $result[] = $header->key . ': ' . $header->value;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getCustomRequestValue(): string
    {
        return match ($this->requestMethod) {
            RequestMethod::GET => 'GET',
            RequestMethod::POST => 'POST',
            RequestMethod::PUT => 'PUT',
            RequestMethod::DELETE => 'DELETE'
        };
    }

    /**
     * Append POST | PUT data / options to CURL.
     *
     * @param CurlHandle $ch
     * @param array $payload
     * @return void
     * @throws JsonException
     */
    private function setContent(CurlHandle $ch, array $payload): void
    {
        if ($this->contentType === ContentType::EMPTY) {
            return;
        }

        $data = $this->getPayloadData(payload: $payload);

        if ($data !== '' && $this->hasBodyData()) {
            curl_setopt(
                handle: $ch,
                option: CURLOPT_POSTFIELDS,
                value: $data
            );
        }

        if ($this->requestMethod === RequestMethod::POST) {
            curl_setopt(
                handle: $ch,
                option: CURLOPT_POST,
                value: true
            );
        }
    }

    /**
     * @param array $payload
     * @return string
     * @throws JsonException
     * @todo Consider caching this is a local variable on this instance to avoid subsequent calls. NOTE: Generating this
     * @todo data directly in the constructor harms refactoring.
     */
    public function getPayloadData(
        array $payload
    ): string {
        return match ($this->contentType) {
            ContentType::EMPTY => '',
            ContentType::JSON => json_encode(
                value: $payload,
                flags: JSON_THROW_ON_ERROR
            ),
            ContentType::URL => http_build_query(data: $payload),
            ContentType::RAW => ''
        };
    }

    /**
     * @return string
     */
    private function getContentType(): string
    {
        return match ($this->contentType) {
            ContentType::EMPTY => 'application/json; charset=utf-8',
            ContentType::JSON => 'application/json; charset=utf-8',
            ContentType::URL => 'application/x-www-form-urlencoded; charset=utf-8',
            ContentType::RAW => 'text/plain; charset=utf-8'
        };
    }

    /**
     * @return string
     * @todo Dropped classname from user agent, didn't seem to make sense, we should however include the version
     * @todo specified in composer.json (see PrestaShop Core psrbcore/src/Traits/Module/Init.php for example).
     * @todo Add back what module class called Curl.
     */
    public function getUserAgent(): string
    {
        return implode(separator: ' +', array: array_filter(array: [
            Config::$instance->userAgent,
            'ECom2-', // @todo Put version from composer.json here.
            sprintf('PHP-%s', PHP_VERSION),
        ]));
    }

    /**
     * @param CurlHandle $ch
     * @return void
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    private function setAuth(CurlHandle $ch): void
    {
        switch ($this->authType) {
            case AuthType::BASIC:
                $this->setBasicAuth(ch: $ch);
                break;
            case AuthType::JWT:
                $this->setJwtAuth(ch: $ch);
                break;
            case AuthType::NONE:
                break;
        }
    }

    /**
     * @param CurlHandle $ch
     * @return void
     * @throws CurlException
     */
    private function setBasicAuth(CurlHandle $ch): void
    {
        $auth = Config::$instance->basicAuth;

        if ($auth === null) {
            throw new CurlException(message: 'Basic auth not configured.');
        }

        curl_setopt(
            handle: $ch,
            option: CURLOPT_USERPWD,
            value: "$auth->username:$auth->password"
        );
    }

    /**
     * @param CurlHandle $ch
     * @return void
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     */
    private function setJwtAuth(CurlHandle $ch): void
    {
        $auth = Config::$instance->jwtAuth;

        if ($auth === null) {
            throw new CurlException(message: 'JWT auth not configured.');
        }

        if ($auth->getToken() === null) {
            $auth->setToken($this->generateJwtToken());
        }

        curl_setopt(
            handle: $ch,
            option: CURLOPT_HTTPAUTH,
            value: CURLAUTH_BEARER
        );

        curl_setopt(
            handle: $ch,
            option: CURLOPT_XOAUTH2_BEARER,
            value: $auth->getToken()->accessToken
        );
    }

    /**
     * @return JwtToken
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     * @todo Needs to be completed, a lot data validation is missing. This should be refactored to a separate class
     * @todo to integrate separate methods to test individual values etc.
     */
    public function generateJwtToken(): JwtToken
    {
        $auth = Config::$instance->jwtAuth;

        if ($auth === null) {
            throw new CurlException(message: 'JWT auth not configured.');
        }

        $tokenRequest = new Curl(
            url: 'api/oauth2/token',
            requestMethod: RequestMethod::POST,
            payload: [
                'client_id' => $auth->clientId,
                'client_secret' => $auth->clientSecret,
                'grant_type' => $auth->grantType,
                'scope' => $auth->scope,
            ],
            authType: AuthType::NONE
        );

        $response = $tokenRequest->exec();

        // @todo This requires MUCH better validation. We must check the type of each property, validate their values
        // @todo using charsets etc. (there are helper functions prepared in lib/Validation, fully tested).
        if (
            !isset($response->body->access_token) ||
            !isset($response->body->token_type) ||
            !isset($response->body->expires_in)
        ) {
            throw new CurlException(message: 'Failed to generate JWT token.');
        }

        return new JwtToken(
            accessToken: $response->body->access_token,
            tokenType: $response->body->token_type,
            expiresIn: $response->body->expires_in,
        );
    }

    /**
     * @return void
     * @throws CurlException
     */
    private function handleError(): void
    {
        $msg = curl_error(handle: $this->ch);
        $code = curl_errno(handle: $this->ch);
        $httpCode = curl_getinfo(handle: $this->ch, option: CURLINFO_HTTP_CODE);

        if ($code !== 0 || $httpCode >= 400) {
            throw new CurlException(
                message: "CURL error (".($code !== 0 ? $code : $httpCode)."): $msg",
                code: ($code !== 0 ? $code : $httpCode)
            );
        }
    }

    /**
     * Returns configured auth credentials as array
     *
     * @return array
     */
    public function getAuthentication(): array
    {
        return match ($this->authType) {
            AuthType::BASIC => (array)Config::$instance->basicAuth,
            AuthType::JWT => (array)Config::$instance->jwtAuth,
            default => [],
        };
    }
}
