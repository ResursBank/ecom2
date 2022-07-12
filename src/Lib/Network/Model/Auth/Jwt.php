<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Model\Auth;

use Exception;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\TypeException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
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
    private const HOSTNAME_PROD = '';
    private const HOSTNAME_TEST = 'apigw-integration.test.resurs.loc';

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
     * @throws TypeException
     */
    private function generateToken(): JwtToken
    {
        $auth = Config::$instance->jwtAuth;

        if ($auth === null) {
            throw new AuthException(message: 'JWT auth not configured.');
        }

        $url = 'https://' . (Config::$instance->isProduction ? self::HOSTNAME_PROD : self::HOSTNAME_TEST)
            . '/api/oauth2/token';

        try {
            $tokenRequest = new Curl(
                url: $url,
                requestMethod: RequestMethod::POST,
                payload: [
                    'client_id' => $auth->clientId,
                    'client_secret' => $auth->clientSecret,
                    'grant_type' => $auth->grantType,
                    'scope' => $auth->scope,
                ],
                authType: AuthType::NONE
            );
        } catch (Exception $exception) {
            throw new AuthException(
                message: 'Unable to create Curl instance: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        try {
            $response = $tokenRequest->exec();
        } catch (Exception $exception) {
            throw new AuthException(
                message: $exception->getMessage(),
                code: $exception->getCode()
            );
        }

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
     * @throws TypeException
     */
    public function getToken(): JwtToken
    {
        if (!$this->token || $this->token->validUntil < time()) {
            $this->token = $this->generateToken();
        }

        return $this->token;
    }
}
