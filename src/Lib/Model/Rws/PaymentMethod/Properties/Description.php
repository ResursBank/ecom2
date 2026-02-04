<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rws\PaymentMethod\Properties;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * RWS payment method properties description.
 */
class Description extends Model
{
    /**
     * @param array $descriptionText
     * @param string $descriptionFormat
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        public readonly array $descriptionText,
        public readonly string $descriptionFormat
    ) {
        parent::__construct();
    }
}
