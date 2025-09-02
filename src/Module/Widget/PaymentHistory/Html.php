<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\PaymentHistory;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PaymentHistory\Translator;

/**
 * Payment history log widget.
 */
class Html extends Widget
{
    public const CACHE_KEY_PREFIX = 'resursbank-ecom-widget-part-payment-html';

    /** @var string */
    public readonly string $content;

    /**
     * @param EntryCollection $entries Log entries to display. The reason for
     *                                 using an entry collection instead of
     *                                 just a payment ID is because allows
     *                                 greater flexibility as the collection can
     *                                 be filtered before rendering the widget.
     * @throws FilesystemException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly EntryCollection $entries,
        public readonly bool $renderButton = true
    ) {
        $this->content = $this->render(
            file: __DIR__ . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * Resolve title content.
     *
     * This is displayed above the entry table. It's intended to reflect
     * relating order/payment and environment.
     *
     * @throws CollectionException
     * @throws FilesystemException
     * @throws JsonException
     * @throws ConfigException
     * @throws TranslationException
     */
    public function getWidgetTitle(): string
    {
        if (!count($this->entries) > 0) {
            return Translator::translate(
                phraseId: 'widget-title-no-payment-id'
            );
        }

        $entry = $this->entries->current();

        return sprintf(
            Translator::translate(phraseId: 'widget-title'),
            $entry instanceof Entry ?
                ((string)$entry->reference !== '' ?
                    $entry->reference :
                    $entry->paymentId) :
                '',
            Translator::translate(
                phraseId: Config::isProduction() ? 'production' : 'test'
            )
        );
    }

    /**
     * Escape and format extra data.
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
     * Get row class based on entry result.
     */
    public function getResultClass(Entry $entry): string
    {
        return match ($entry->result) {
            Result::SUCCESS => 'success-entry',
            Result::ERROR => 'error-entry',
            Result::INFO => 'info-entry'
        };
    }

    /**
     * If the extra content is shorter than 40 characters, hide extra button.
     *
     * The extra content will instead be displayed directly in the table column.
     */
    public function showExtraBtn(Entry $entry): bool
    {
        return $entry->extra !== null && strlen(string: $entry->extra) > 40;
    }

    /**
     * Resolve content for user column as "Entry.user (Entry.userReference)"
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws JsonException
     * @throws TranslationException
     */
    public function getUser(Entry $entry): string
    {
        $result = Translator::translate(phraseId: $entry->user->value);

        if ((string) $entry->userReference !== '') {
            $result .= " ($entry->userReference)";
        }

        return $result;
    }
}
