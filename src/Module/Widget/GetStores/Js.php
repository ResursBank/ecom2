<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Widget\GetStores;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Generate JavaScript code to fetch list of stores and update select element.
 *
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class Js extends Widget
{
    public const CACHE_KEY_PREFIX =
        'resursbank-ecom-widget-get-stores-js';

    /** @var string */
    public readonly string $content;

    /**
     * @param bool $automatic Automatically search on field change
     * @param string|null $storeSelectId ID of element to populate with new stores.
     * @param string|null $environmentSelectId ID of element containing environments.
     * @param string|null $clientIdInputId ID of client id input element.
     * @param string|null $clientSecretInputId ID of client secret element.
     * @param string|null $spinnerClass Class applied on store select element when fetching.
     * @param string|null $fetchBtnId ID of button to trigger fetch.
     * @param bool $createBtn Whether to create a fetch button since some platforms has no generic way to create one.
     * @throws FilesystemException
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly bool $automatic = false,
        public readonly ?string $storeSelectId = null,
        public readonly ?string $environmentSelectId = null,
        public readonly ?string $clientIdInputId = null,
        public readonly ?string $clientSecretInputId = null,
        public readonly ?string $spinnerClass = null,
        public readonly ?string $fetchBtnId = null,
        public readonly bool $createBtn = false,
        public readonly bool $silent = false
    ) {
        $this->content = $this->render(
            file: $this->getWidgetName() . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'js.js.phtml'
        );
    }
}
