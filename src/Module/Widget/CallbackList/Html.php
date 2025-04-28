<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\CallbackList;

use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Callback URL list widget.
 */
class Html extends Widget
{
    /** @var string */
    public readonly string $content;

    public function __construct(
        private readonly ?string $authorizationUrl = null,
        private readonly ?string $managementUrl = null
    ) {
        $this->content = $this->render(file: __DIR__ . '/callback.phtml');
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
}
