<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Store;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Get stores request model.
 *
 * Request model to collect stores based on credentials. Useful for AJAX
 * requests to collect a list of available stores before credentials are
 * actually saved (enter credentials, reload list of stores, select store).
 */
class GetStoresRequest extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        public readonly Environment $environment,
        #[StringNotEmpty] public readonly string $clientId,
        #[StringNotEmpty] public readonly string $clientSecret
    ) {
        parent::__construct();
    }
}
