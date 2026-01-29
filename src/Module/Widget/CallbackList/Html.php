<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CallbackList;

use JsonException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Callback URL list widget.
 */
class Html extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function __construct(
        private readonly ?string $authorizationUrl = null,
        private readonly ?string $managementUrl = null,
        private readonly ?string $creditApplicationUrl = null
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'html.phtml'
        );
    }

    /**
     * @throws ConfigException
     * @throws TranslationException
     * @throws FilesystemException
     * @throws JsonException
     */
    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    /**
     * @throws ConfigException
     * @throws TranslationException
     * @throws JsonException
     * @throws FilesystemException
     */
    public function getManagementUrl(): ?string
    {
        return $this->managementUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    public function getCreditApplicationUrl(): ?string
    {
        return $this->creditApplicationUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }
}
