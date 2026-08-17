<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\UserSettings;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * User settings metadata model.
 *
 * This model contains data such as the store identifier for the config reader.
 * E.g., the store view id to be used when reading / writing config data in
 * the M2 integration and similar.
 */
class Metadata extends Model
{
    /**
     * @param string|null $storeId - Store identifier in integration, not to be confused with API account store.
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(
        #[StringNotEmpty] public readonly ?string $storeId = null
    ) {
        parent::__construct();
    }
}
