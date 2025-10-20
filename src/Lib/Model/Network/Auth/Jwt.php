<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Auth;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt\Token;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Throwable;

/**
 * Defines JSON Token API authentication.
 */
class Jwt extends Model
{
    use ModelConverter;
    use DataResolver;

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @todo Add charset validation of id and secret.
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $clientId,
        #[StringNotEmpty] public readonly string $clientSecret,
        public readonly GrantType $grantType = GrantType::CREDENTIALS,
        public readonly Scope $scope = Scope::MERCHANT_API,
        public readonly bool $cacheToken = false,
        private ?Token $token = null,
        private readonly Mapi $mapi = new Mapi()
    ) {
        parent::__construct();
    }

    /**
     * @throws ConfigException
     */
    public function setToken(?Token $token): void
    {
        $this->token = $token;

        if (!$this->cacheToken || $this->token === null) {
            return;
        }

        $this->setCachedToken();
    }

    /**
     * Token getter.
     *
     * @throws ConfigException
     */
    public function getToken(): ?Token
    {
        if ($this->cacheToken && $this->token === null) {
            $this->token = $this->getCachedToken();
        }

        return $this->token;
    }

    /**
     * Attempt to find and load token from cache.
     *
     * @throws ConfigException
     */
    private function getCachedToken(): ?Token
    {
        $data = Config::getCache()->read(key: $this->getCacheKey());

        $result = null;

        try {
            if ($data !== null) {
                /** @var Token $result */
                $result = $this->convertToModel(
                    data: $data,
                    model: Token::class
                );

                if (!$result instanceof Token) {
                    throw new IllegalTypeException(
                        message: 'Loaded token is not of type ' . Token::class .
                        ', this should not happen.'
                    );
                }
            }
        } catch (Throwable $error) {
            Config::getLogger()->error($error->getMessage());
        }

        return $result;
    }

    /**
     * Save token to cache.
     *
     * @throws ConfigException
     */
    private function setCachedToken(): void
    {
        try {
            if ($this->token === null) {
                throw new EmptyValueException(
                    message: 'Unable to save null token to cache.'
                );
            }

            Config::getCache()->write(
                key: $this->getCacheKey(),
                data: json_encode(
                    value: $this->token->toArray(full: true),
                    flags: JSON_THROW_ON_ERROR
                ),
                ttl: $this->token->expires_at - time()
            );
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
        }
    }

    /**
     * Generate cache key.
     */
    private function getCacheKey(): string
    {
        return AbstractCache::getKey(
            key: 'token-' . sha1(string: implode(
                separator: '-',
                array: [
                    $this->clientId,
                    $this->grantType->value,
                    $this->scope->value,
                ]
            ))
        );
    }
}
