<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * English phrase that can be translated into any language.
 */
class Phrase extends Model
{
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/i'
        )] public string $id,
        public Translation $translation
    ) {
        parent::__construct();
    }
}
