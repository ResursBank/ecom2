<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Model\Auth;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Lib\Network\Model\JwtToken;

/**
 * Defines JSON Token API authentication.
 */
class Jwt
{
    /**
     * @param string $id
     * @param string $secret
     * @param string $scope
     * @param string $grantType
     * @param JwtToken|null $token
     * @param StringValidation $stringValidation
     * @throws EmptyValueException
     * @todo Add charset validation of id and secret.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $secret,
        public readonly string $scope,
        public readonly string $grantType,
        private JwtToken|null $token,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->notEmpty(value: $this->secret);
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
     * @return JwtToken|null
     */
    public function getToken(): ?JwtToken
    {
        return $this->token;
    }
}
