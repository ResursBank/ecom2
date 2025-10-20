<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\ReadMore;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLink;
use Resursbank\Ecom\Lib\Model\PriceSignage\UriLink;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PriceSignage\Repository;
use Throwable;

/**
 * Read more widget.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-read-more-html';

    public string $url = '';

    /** @var string */
    public readonly string $content;

    /**
     * @param bool $useLegacyLink Use legacy link instead of SECCI if true.
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly float $amount,
        private readonly bool $useLegacyLink = false
    ) {
        if (!$this->paymentMethod->isInternal()) {
            $this->content = '';
            return;
        }

        $this->url = $this->useLegacyLink ?
            $this->getLegacyUrl() : $this->getSecciUrl();

        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Fetch SECCI URL.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public function getSecciUrl(): string
    {
        $links = Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->amount
        );

        /** @var UriLink $secciLink */
        foreach ($links->secciLinks as $secciLink) {
            if (
                $secciLink->language ===
                Config::getLanguage()->toPriceSignageLanguage()
            ) {
                return $secciLink->uri;
            }
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function shouldRender(): bool
    {
        return $this->url !== '';
    }

    /**
     * Fetch legacy link.
     */
    public function getLegacyUrl(): string
    {
        /** @var LegalLink $link */
        foreach ($this->paymentMethod->legalLinks as $link) {
            if ($link->type !== Type::PRICE_INFO) {
                continue;
            }

            return $link->url . $this->amount;
        }

        return '';
    }
}
