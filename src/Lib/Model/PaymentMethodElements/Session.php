<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethodElements;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\PaymentMethodElements\Session\Embed;

class Session extends Model
{
    public function __construct(
        public readonly string $id,
        public readonly string $expiresAt,
        public readonly Embed $widgetEmbed
    ) {
        parent::__construct();
    }

    public function expired(): bool
    {
        $timestamp = strtotime($this->expiresAt);
        return $timestamp < time();
    }
}
