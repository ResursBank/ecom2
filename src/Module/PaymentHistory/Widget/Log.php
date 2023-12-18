<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Payment methods table widget.
 */
class Log extends Widget
{
    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /**
     * @param EntryCollection $entries
     * @throws FilesystemException
     */
    public function __construct(
        public readonly EntryCollection $entries
    ) {
        $this->content = $this->render(file: __DIR__ . '/log.phtml');
        $this->css = $this->render(file: __DIR__ . '/log.css');
    }

    /**
     * @return string
     * @throws CollectionException
     * @throws FilesystemException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws TranslationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function getWidgetTitle(): string
    {
        return sprintf(
            Translator::translate(phraseId: 'widget-title'),
            (string) $this->entries->current()?->paymentId
        );
    }

    /**
     * Format extra data with <br/> elements and escaped double quotes.
     */
    public function getExtraData(Entry $entry): string
    {
        return str_replace(
            search: ["\n", "\r"],
            replace: '',
            subject: nl2br(
                string: htmlspecialchars(
                    string: addslashes(string: (string) $entry->extra),
                    flags: ENT_QUOTES,
                    encoding: 'UTF-8'
                )
            )
        );
    }

    /**
     * Resolve CSS class for status icon element.
     */
    public function getStatusClass(Entry $entry): string
    {
        return strtolower(string: $entry->type->value) . '-icon';
    }
}
