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
    /** en_EN */
    case en = 'en';

    /** se_SV */
    case sv = 'sv';

    /**
     * Remember on integrations where Norwegian locales are not defined as NO.
     * Norwegian may have multiple definitions (e.g., nb-Norwegian Bokmål, nn-Nynorsk).
     * no_NO / nb_NO / nn_NO
     */
    case no = 'no';

    /** fi_FI */
    case fi = 'fi';

    /** dk_DA */
    case da = 'da';
}
