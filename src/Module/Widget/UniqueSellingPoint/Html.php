<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\UniqueSellingPoint;

use JsonException;
use ReflectionException;
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
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Widget\ReadMore\Html as ReadMoreHtml;
use Throwable;

/**
 * Unique Selling Point fetcher
 */
class Html extends Widget
{
    /** @var ReadMoreHtml */
    public readonly ReadMoreHtml $readMore;

    /** @var string */
    public readonly string $content;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws Throwable
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly float $amount,
        public readonly bool $useLegacyReadMoreLink = false
    ) {
        $this->readMore = new ReadMoreHtml(
            paymentMethod: $this->paymentMethod,
            amount: $amount,
            useLegacyLink: $this->useLegacyReadMoreLink
        );
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Fetches the localized USP translation for a payment method type.
     *
     * This uses a module-specific translation file as the API does not contain
     * the necessary USP data.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     */
    public function getText(): string
    {
        return Translator::translate(
            phraseId: str_replace(
                search: '_',
                replace: '-',
                subject: strtolower($this->paymentMethod->type->value)
            ),
            translationFile: __DIR__ . DIRECTORY_SEPARATOR . 'Resources' .
            DIRECTORY_SEPARATOR . 'translations.json'
        );
    }
}
