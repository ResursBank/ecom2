<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Probe;

use Attribute;

/**
 * Mark class a probable.
 *
 * Marking a class as probable makes it subject to automatic unit testing of
 * properties based on attached validation attributes.
 */
#[Attribute(flags: Attribute::TARGET_CLASS)]
class Probable
{
}
