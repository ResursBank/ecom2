<?php

/**
* Copyright © Resurs Bank AB. All rights reserved.
* See LICENSE for license details.
*/

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of TrackingDto object.
 */
class Tracking extends Model
{
    public function __construct(
        public readonly ?string $url = null
    ) {
    }
}
