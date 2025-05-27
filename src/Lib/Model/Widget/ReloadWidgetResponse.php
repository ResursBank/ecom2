<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Widget;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Response object for AJAX requests used to reload widget content.
 */
class ReloadWidgetResponse extends Model
{
    public function __construct(
        public readonly string $html,
    ) {
        parent::__construct();
    }
}
