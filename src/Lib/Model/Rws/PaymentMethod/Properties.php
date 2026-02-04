<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws\PaymentMethod;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rws\PaymentMethod\Properties\Description;

/**
 * RWS payment method properties.
 */
class Properties extends Model
{
    /**
     * @param string|null $title
     * @param string|null $subtitle
     * @param Description|null $description
     * @param string|null $footer
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $subtitle = null,
        public readonly ?Description $description = null,
        public readonly ?string $footer = null
    ) {
        parent::__construct();
    }
}