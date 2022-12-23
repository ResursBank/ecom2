<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Auth;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt\Token;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines JSON Token API authentication.
 */
class Jwt extends Model
{
    use ModelConverter;
    use DataResolver;

    /**
     * @throws EmptyValueException
     * @todo Add charset validation of id and secret.
     */
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $scope,
        public readonly string $grantType,
        private ?Token $token = null,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly Mapi $mapi = new Mapi()
    ) {
        $this->validateClientId();
        $this->validateClientSecret();
        $this->validateScope();
        $this->validateGrantType();
    }

    /**
     * @throws EmptyValueException
     */
    public function validateClientId(): void
    {
        $this->stringValidation->notEmpty(value: $this->clientId);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateClientSecret(): void
    {
        $this->stringValidation->notEmpty(value: $this->clientSecret);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateScope(): void
    {
        $this->stringValidation->notEmpty(value: $this->scope);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateGrantType(): void
    {
        $this->stringValidation->notEmpty(value: $this->grantType);
    }

    public function setToken(?Token $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }
}
