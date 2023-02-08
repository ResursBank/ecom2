<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Debug\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Payment methods table widget.
 */
class Info extends Widget
{
    /**
     * HTML content.
     */
    public readonly string $content;

    /**
     * Render widget content.
     *
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $pluginVersion,
        public readonly PaymentMethodCollection $paymentMethods
    ) {
        $this->content = $this->render(file: __DIR__ . '/info.phtml');
    }

    /**
     * Shortcut method to translate using module specific translation file.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function translate(string $id): string
    {
        return Translator::translate(
            phraseId: $id,
            translationFile: __DIR__ . '../Resources/translations.json'
        );
    }
}
