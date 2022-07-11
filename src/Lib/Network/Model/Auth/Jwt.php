<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Model\Auth;

use Exception;
use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Network\Model\JwtToken;
use stdClass;

/**
 * Defines JSON Token API authentication.
 */
class Jwt
{
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $scope
     * @param string $grantType
     * @param JwtToken|null $token
     * @param StringValidation $stringValidation
     * @throws EmptyValueException
     * @todo Add charset validation of id and secret.
     */
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $scope,
        public readonly string $grantType,
        private JwtToken|null $token = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->stringValidation->notEmpty(value: $this->clientId);
        $this->stringValidation->notEmpty(value: $this->clientSecret);
    }

    /**
     * @param JwtToken|null $token
     * @return void
     */
    public function setToken(JwtToken|null $token): void
    {
        $this->token = $token;
    }

    /**
     * @return JwtToken
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws JsonException
     * @throws ValidationException
     * @throws IllegalTypeException
     * @throws TypeException
     */
    private function generateJwtToken(): JwtToken
    {
        $auth = Config::$instance->jwtAuth;

        if ($auth === null) {
            throw new AuthException(message: 'JWT auth not configured.');
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
        if (!$response->body instanceof stdClass) {
            throw new AuthException(message: 'Response body type is ' . gettype(value: $response->body)
                . 'expected stdClass');
        }

        if (
            !isset($response->body->access_token) ||
            !isset($response->body->token_type) ||
            !isset($response->body->expires_in)
        ) {
            throw new AuthException(message: 'Failed to generate JWT token.');
        }

        if (!is_numeric(value: $response->body->expires_in) || !is_int(value: $response->body->expires_in)) {
            throw new TypeException(
                message: 'Received invalid expires_in value (' . $response->body->expires_in
                    . '), was expecting integer'
            );
        }

        return new JwtToken(
            accessToken: $response->body->access_token,
            tokenType: $response->body->token_type,
            validUntil: time() + $response->body->expires_in,
        );
    }

    /**
     * Returns token, if we have no token or the current token is expired we fetch a new one
     *
     * @return JwtToken
     * @throws AuthException
     */
    public function getToken(): JwtToken
    {
        if (!$this->token || $this->token->validUntil < time()) {
            try {
                $this->token = $this->generateJwtToken();
            } catch (Exception $exception) {
                throw new AuthException(message: $exception->getMessage());
            }
        }

        return $this->token;
    }
}
