<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory\Widget;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TestException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use Resursbank\Ecom\Module\PaymentHistory\Widget\Log;

/**
 * Tests widget rendering.
 */
class LogTest extends TestCase
{
    /**
     * Log instance used in various tests.
     */
    private Log $log;

    /**
     * @throws AttributeCombinationException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            cache: new None(),
            jwtAuth: new Jwt(
                clientId: $_ENV['JWT_AUTH_CLIENT_ID'],
                clientSecret: $_ENV['JWT_AUTH_CLIENT_SECRET'],
                scope: Scope::from(value: $_ENV['JWT_AUTH_SCOPE']),
                grantType: GrantType::from(value: $_ENV['JWT_AUTH_GRANT_TYPE'])
            )
        );

        $this->log = new Log(entries: $this->getEntries());

        parent::setUp();
    }

    /**
     * Wrapper to generate Entry model instance.
     *
     * @throws AttributeCombinationException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    private function getEntry(
        ?Result $result = null,
        ?string $extra = null,
        ?string $reference = null,
        ?string $userReference = null
    ): Entry {
        if ($result === null) {
            $result = $this->getRandomResult();
        }

        if ($extra === null) {
            $extra = $this->getRandomExtra(type: $result);
        }

        if ($reference === null) {
            $reference = Random::getString(length: 50);
        }

        if ($userReference === null) {
            $userReference = Random::getString(length: 50);
        }

        return new Entry(
            paymentId: Strings::getUuid(),
            event: $this->getRandomEvent(),
            user: $this->getRandomUser(),
            result: $result,
            extra: $extra,
            previousOrderStatus: $this->getRandomStatus(),
            currentOrderStatus: $this->getRandomStatus(),
            time: time(),
            userReference: $userReference,
            reference: $reference
        );
    }

    /**
     * Resolve collection of log entries.
     *
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Exception
     */
    private function getEntries(): EntryCollection
    {
        $data = [];
        $size = rand(min: 1, max: 50);

        for ($i = 0; $i < $size; $i++) {
            $data[] = $this->getEntry();
        }

        return new EntryCollection(data: $data);
    }

    private function getRandomEvent(): Event
    {
        $cases = Event::cases();
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomUser(): User
    {
        $cases = User::cases();
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomResult(): Result
    {
        $cases = Result::cases();
        /* @phpstan-ignore-next-line */
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomExtra(Result $type): string
    {
        // If $type is SUCCESS, return an empty string.
        if ($type === Result::SUCCESS) {
            return '';
        }

        // If $type is ERROR, create a spoofed Exception and return its trace.
        if ($type === Result::ERROR) {
            $exception = new TestException(message: 'Spoofed Exception');
            return $exception->getTraceAsString();
        }

        // For INFO type.
        $maxLength = 2400;

        $words = [
            'Lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
            'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
            'magna', 'aliqua', 'Ut', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
            'exercitation', 'ullamco', 'laboris', 'nisi', 'ut', 'aliquip', 'ex', 'ea',
            'commodo', 'consequat', 'Duis', 'aute', 'irure', 'dolor', 'in', 'reprehenderit',
            'in', 'voluptate', 'velit', 'esse', 'cillum', 'dolore', 'eu', 'fugiat', 'nulla',
            'pariatur', 'Excepteur', 'sint', 'occaecat', 'cupidatat', 'non', 'proident', 'sunt',
            'in', 'culpa', 'qui', 'officia', 'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum'
        ];

        // Generate and return the lorem ipsum text.
        $loremIpsumText = '';

        while (strlen(string: $loremIpsumText) < $maxLength) {
            /* @phpstan-ignore-next-line */
            $randomWord = $words[array_rand(array: $words)];
            $loremIpsumText .= ' ' . $randomWord;
        }

        return substr(string: $loremIpsumText, offset: 0, length: $maxLength);
    }

    private function getRandomStatus(): string
    {
        $statuses = [
            'Pending payment (pending)',
            'Processing (processing)',
            'Closed (closed)',
        ];

        /* @phpstan-ignore-next-line */
        return $statuses[array_rand(array: $statuses)];
    }

    /**
     * Assert rendering logs widget works as expected.
     *
     * @throws AttributeCombinationException
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
                default => '',
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

        // Create an HTML file and write the content to it.
        $htmlFileName = '/etc/waddle/project/rendered_log_widget.html';
        file_put_contents(filename: $htmlFileName, data: $content);
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
     * Make sure the
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
     * Assert that extra content with more than 40 characters results in the
     * content being displayed directly in the table.
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

        $this->assertEmpty(actual: $this->log->getResultClass(entry: $info));
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
