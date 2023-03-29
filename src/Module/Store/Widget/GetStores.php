<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Generate JavaScript code to fetch list of stores and update select element.
 */
class GetStores extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param string $fetchUrl Endpoint where we fetch stores.
     * @param string $environmentSelectId ID of element containing environments.
     * @param string $clientIdInputId ID of client id input element.
     * @param string $clientSecretInputId ID of client secret element.
     * @param string $storeSelectId ID of element to populate with new stores.
     * @param string $spinnerClass Class applied on store select element when fetching.
     * @throws FilesystemException
     */
    public function __construct(
        public readonly string $fetchUrl,
        public readonly string $environmentSelectId,
        public readonly string $clientIdInputId,
        public readonly string $clientSecretInputId,
        public readonly string $storeSelectId,
        public readonly string $spinnerClass
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-stores.js.phtml');
    }
}
