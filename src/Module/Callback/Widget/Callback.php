<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Callback URL list widget.
 */
class Callback extends Widget
{
    /** @var string */
    public readonly string $content;

    public function __construct(
        private readonly ?string $authorizationUrl = null,
        private readonly ?string $managementUrl = null
    ) {
        $this->renderWidget();
    }

    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    public function getManagementUrl(): ?string
    {
        return $this->managementUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    /**
     * Render widget content.
     *
     * @throws FilesystemException
     */
    protected function renderWidget(): void
    {
        $this->content = $this->render(file: __DIR__ . '/callback.phtml');
    }
}
