<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PriceSignage\Widget;

use Resursbank\Ecom\Lib\Widget\Widget;

class CostListJs extends Widget
{
    /** @var string */
    public readonly string $content;

    public function __construct(
        public readonly string $containerElDomPath,
        public readonly bool $auto = true,
    ) {
        $this->content = $this->render(file: __DIR__ . '/cost-list.js.phtml');
    }
}
