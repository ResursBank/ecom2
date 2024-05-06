<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback\Widget;

use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;

class Callback extends Widget
{
    public readonly string $content;

    public function __construct(
        private readonly ?string $authorizationUrl = null,
        private readonly ?string $managementUrl = null
    )
    {
        $this->renderWidget();
    }

    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl;
    }

    public function getManagementUrl(): ?string
    {
        return $this->managementUrl;
    }

    protected function renderWidget(): void
    {
        $this->content = $this->render(
            file: __DIR__ . '/callback.phtml'
        );
    }
}
