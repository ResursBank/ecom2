<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

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
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Language;
use Resursbank\Ecom\Lib\Model\PriceSignage\UriLink;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PriceSignage\Repository;
use Throwable;

/**
 * Read more widget.
 */
class ReadMore extends Widget
{
    public string $url = '';

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $label;

    /**
     * @param string $label Translation ID to use for widget label.
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
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly float $amount,
        string $label = 'read-more'
    ) {
        $this->url = '';

        if ($this->paymentMethod->priceSignagePossible) {
            $this->setUrl();
        }

        $this->label = Translator::translate(phraseId: $label);
        $this->content = $this->render(file: __DIR__ . '/read-more.phtml');
        $this->css = $this->render(file: __DIR__ . '/read-more.css');
    }

    /**
     * For implementations where content and resources needs separation.
     *
     * @throws FilesystemException
     */
    public static function getCss(): string
    {
        return (new Widget())->render(file: __DIR__ . '/read-more.css');
    }

    /**
     * @throws ConfigException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws Throwable
     */
    private function setUrl(): void
    {
        $links = Repository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->amount
        );

        /** @var UriLink $secciLink */
        foreach ($links->secciLinks as $secciLink) {
            if (
                !$this->isConfigLanguage(secciLanguage: $secciLink->language)
            ) {
                continue;
            }

            $this->url = $secciLink->uri;
        }
    }

    /**
     * Check if provided UriLink Language is the same as the Ecom language.
     *
     * @param Language $secciLanguage SECCI language enum.
     * @return bool True if both languages are the same.
     * @throws ConfigException
     */
    private function isConfigLanguage(
        Language $secciLanguage
    ): bool {
        $comparisonTable = [
            'Swedish' => 'sv',
            'Norwegian' => 'no',
            'Finnish' => 'fi',
            'Danish' => 'da'
        ];

        return
            array_key_exists(
                key: $secciLanguage->value,
                array: $comparisonTable
            )
            && $comparisonTable[$secciLanguage->value] === Config::getLanguage()->value
        ;
    }
}
