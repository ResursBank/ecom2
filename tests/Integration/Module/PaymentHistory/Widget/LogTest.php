<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Module\PaymentHistory\Widget;

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
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Type;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Utilities\Strings;
use Resursbank\Ecom\Module\PaymentHistory\Widget\Log;

/**
 * Tests widget rendering.
 */
class LogTest extends TestCase
{

    /**
     * @throws EmptyValueException
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

        parent::setUp();
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
        $entries = $this->getEntries();
        $log = new Log(entries: $entries);
        $content = $log->content;

        foreach ($entries as $entry) {
            // Check if the correct classes are applied based on the entry type.
            $typeClass = match ($entry->type) {
                Type::SUCCESS => 'success-entry',
                Type::ERROR => 'error-entry',
                default => '',
            };

            $this->assertStringContainsString(
                needle: "<tr class=\"$typeClass\">",
                haystack: $content,
                message: "Entry type class not found for entry with type: {$entry->type->value}"
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
        }

        // Check the presence of other elements in the HTML content.
        $this->assertStringContainsString(needle: '<div id="rb-ph-log">', haystack: $content);
        $this->assertStringContainsString(needle: '<table id="rb-ph-log-table">', haystack: $content);

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
            new Entry(
                paymentId: Strings::getUuid(),
                event: $this->getRandomEvent(),
                user: $this->getRandomUser(),
                type: Type::SUCCESS,
                extra: '',
                previousOrderStatus: $this->getRandomStatus(),
                currentOrderStatus: $this->getRandomStatus()
            )
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
        // Arrange
        $entries = new EntryCollection(data: [
            new Entry(
                paymentId: Strings::getUuid(),
                event: $this->getRandomEvent(),
                user: $this->getRandomUser(),
                type: Type::ERROR,
                extra: 'Spoofed Exception',
                previousOrderStatus: $this->getRandomStatus(),
                currentOrderStatus: $this->getRandomStatus()
            )
        ]);

        // Assert
        $this->assertStringContainsString(
            needle: '<tr class="error-entry">',
            haystack: (new Log(entries: $entries))->content,
            message: 'ERROR entry not found in HTML content.'
        );
    }

    /**
     * Resolve collection of log entries.
     *
     * @return EntryCollection
     * @throws AttributeCombinationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function getEntries(): EntryCollection
    {
        $data = [];
        $size = rand(min: 1, max: 50);

        for ($i = 0; $i < $size; $i++) {
            $type = $this->getRandomType();
            $data[] = new Entry(
                paymentId: Strings::getUuid(),
                event: $this->getRandomEvent(),
                user: $this->getRandomUser(),
                type: $type,
                extra: $this->getRandomExtra(type: $type),
                previousOrderStatus: $this->getRandomStatus(),
                currentOrderStatus: $this->getRandomStatus()
            );
        }

        return new EntryCollection(data: $data);
    }

    private function getRandomEvent(): Event
    {
        $cases = Event::cases();
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomUser(): User
    {
        $cases = User::cases();
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomType(): Type
    {
        $cases = Type::cases();
        return $cases[array_rand(array: $cases)];
    }

    private function getRandomExtra(Type $type): string
    {
        // If $type is SUCCESS, return an empty string.
        if ($type === Type::SUCCESS) {
            return '';
        }

        // If $type is ERROR, create a spoofed Exception and return its trace.
        if ($type === Type::ERROR) {
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

        return $statuses[array_rand(array: $statuses)];
    }
}
