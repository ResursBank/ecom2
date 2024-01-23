<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory\Widget;

use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use Resursbank\Ecom\Module\PaymentHistory\Widget\Log;
use Resursbank\EcomTest\Utilities\PaymentHistory;

/**
 * Tests widget rendering.
 */
class LogTest extends PaymentHistory
{
    /**
     * Log instance used in various tests.
     */
    private Log $log;

    /**
     * @throws AttributeCombinationException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        Config::setup();

        $this->log = new Log(entries: $this->getEntries());

        parent::setUp();
    }

    /**
     * Assert rendering log widget works as expected.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testRenderLogWidget(): void
    {
        $content = $this->log->content;

        /** @var Entry $entry */
        foreach ($this->log->entries as $entry) {
            // Check if the correct classes are applied based on the entry type.
            $typeClass = match ($entry->result) {
                Result::SUCCESS => 'success-entry',
                Result::ERROR => 'error-entry',
                Result::INFO => 'info-entry'
            };

            $this->assertStringContainsString(
                needle: "<tr class=\"$typeClass\">",
                haystack: $content,
                message: "Entry type class not found for entry with type: {$entry->result->value}"
            );

            // Check translated phrases for entry event, user, and value.
            $this->assertStringContainsString(
                needle: Translator::translate(phraseId: $entry->event->value),
                haystack: $content,
                message: "Translated phrase not found for event value: {$entry->event->value}"
            );

            $this->assertStringContainsString(
                needle: Translator::translate(phraseId: $entry->user->value),
                haystack: $content,
                message: "Translated phrase not found for user: {$entry->user->value}"
            );

            $user = Translator::translate(phraseId: $entry->user->value);
            $ref = $entry->userReference;

            $this->assertStringContainsString(
                needle: "$user ($ref)",
                haystack: $content
            );
        }

        // Check the presence of other elements in the HTML content.
        $this->assertStringContainsString(
            needle: '<div id="rb-ph-log">',
            haystack: $content
        );
        $this->assertStringContainsString(
            needle: '<table id="rb-ph-log-table">',
            haystack: $content
        );

        // Check translated phrases.
        $translatedPhrases = [
            'event',
            'user',
            'previous-status',
            'current-status',
            'extra',
            'go-back',
            'show-extra',
        ];

        foreach ($translatedPhrases as $phraseId) {
            $this->assertStringContainsString(
                needle: Translator::translate(phraseId: $phraseId),
                haystack: $content,
                message: "Translated phrase not found for phraseId: $phraseId"
            );
        }
    }

    /**
     * Assert rendering an entry with type SUCCESS produces correct HTML.
     *
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function testRenderSuccessEntry(): void
    {
        $entries = new EntryCollection(data: [
            $this->getEntry(result: Result::SUCCESS)
        ]);

        $this->assertStringContainsString(
            needle: '<tr class="success-entry">',
            haystack: (new Log(entries: $entries))->content,
            message: 'SUCCESS entry not found in HTML content.'
        );
    }

    /**
     * Assert rendering an entry with type ERROR produces correct HTML.
     *
     * @throws AttributeCombinationException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testRenderErrorEntry(): void
    {
        $entries = new EntryCollection(data: [
            $this->getEntry(result: Result::ERROR)
        ]);

        $this->assertStringContainsString(
            needle: '<tr class="error-entry">',
            haystack: (new Log(entries: $entries))->content,
            message: 'ERROR entry not found in HTML content.'
        );
    }

    /**
     * Make sure that user value is rendered with userReference if it exists.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws Exception
     */
    public function testGetUser(): void
    {
        /** @var Entry $entry */
        foreach ($this->log->entries as $entry) {
            $user = Translator::translate(phraseId: $entry->user->value);
            $ref = $entry->userReference;

            $this->assertSame(
                actual: $this->log->getUser(entry: $entry),
                expected: "$user ($ref)"
            );
        }

        // Assert only user type is returned when userReference isn't provided.
        $entry = $this->getEntry(userReference: '');

        $this->assertSame(
            actual: $this->log->getUser(entry: $entry),
            expected: Translator::translate(phraseId: $entry->user->value)
        );
    }

    /**
     * Assert that extra content with less or equal to than 40 characters
     * results in the content being displayed directly in the table.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws FilesystemException
     * @throws JsonException
     * @throws Exception
     */
    public function testShortExtraContent(): void
    {
        $extra = Random::getString(length: 40);
        $entry = $this->getEntry(extra: $extra);
        $log = new Log(entries: new EntryCollection(data: [$entry]));

        $this->assertMatchesRegularExpression(
            pattern: '/<td>\s*' . $extra . '\s*<\/td>/m',
            string: $log->content,
            message: 'Missing td element containing extra info.'
        );

        $this->assertStringNotContainsString(
            needle: 'rb-rh-show-extra-btn',
            haystack: $log->content
        );

        $this->assertFalse(
            condition: $log->showExtraBtn(entry: $entry)
        );
    }

    /**
     * Assert that extra content with more than 40 characters results in a
     * button being displayed, which when clicked displays the extra content.
     *
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws FilesystemException
     * @throws JsonException
     * @throws Exception
     */
    public function testLongExtraContent(): void
    {
        $extra = Random::getString(length: 100);
        $entry = $this->getEntry(extra: $extra);
        $log = new Log(entries: new EntryCollection(data: [$entry]));

        $this->assertDoesNotMatchRegularExpression(
            pattern: '/<td>\s*' . $extra . '\s*<\/td>/m',
            string: $log->content,
            message: 'Missing td element containing extra info.'
        );

        $this->assertStringContainsString(
            needle: 'rb-rh-show-extra-btn',
            haystack: $log->content
        );

        $this->assertTrue(
            condition: $log->showExtraBtn(entry: $entry)
        );
    }

    /**
     * Assert getResultClass method resolve accurate class based on result.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetResultClass(): void
    {
        $info = $this->getEntry(result: Result::INFO);
        $success = $this->getEntry(result: Result::SUCCESS);
        $error = $this->getEntry(result: Result::ERROR);

        $this->assertSame(
            actual: $this->log->getResultClass(entry: $info),
            expected: 'info-entry'
        );
        $this->assertSame(
            actual: $this->log->getResultClass(entry: $success),
            expected: 'success-entry'
        );
        $this->assertSame(
            actual: $this->log->getResultClass(entry: $error),
            expected: 'error-entry'
        );
    }

    /**
     * Assert widget title is generated correctly depending on data provided to
     * the first entry model in the assigned entry collection.
     *
     * @throws AttributeCombinationException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws Exception
     */
    public function testGetWidgetTitle(): void
    {
        $entry = $this->getEntry();
        $log = new Log(entries: new EntryCollection(data: [$entry]));

        $this->assertStringContainsString(
            needle: 'Payment #' . $entry->reference . ' [' .
                Translator::translate(
                    phraseId: Config::isProduction() ? 'production' : 'test'
                ) . ']',
            haystack: $log->content
        );

        $entry = $this->getEntry(reference: '');
        $log = new Log(entries: new EntryCollection(data: [$entry]));

        $this->assertStringContainsString(
            needle: 'Payment #' . $entry->paymentId . ' [' .
            Translator::translate(
                phraseId: Config::isProduction() ? 'production' : 'test'
            ) . ']',
            haystack: $log->content
        );
    }

    /**
     * Assert the method getExtraData properly escapes and converts data.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testGetExtraData(): void
    {
        $entry = $this->getEntry(extra: "A'simple-test\n\r");

        $this->assertSame(
            expected: "A\\&#039;simple-test<br />",
            actual: $this->log->getExtraData(entry: $entry)
        );
    }
}
