<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

/**
 * @codingStandardsIgnoreStart
 */
enum Language: string
{
    case en = 'en';
    case sv = 'sv';
    /** Norwegian may have multiple definitions (i.e. nb-norsk bokmål, nn-nynorsk, no="default"). */
    case no = 'no';
    case fi = 'fi';
    case da = 'da';
}
