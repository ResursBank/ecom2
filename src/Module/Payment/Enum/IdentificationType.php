<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Enum;

/**
 * Customer identification types.
 */
enum IdentificationType: string
{
    case ID = 'ID';
    case DRIVERS_LICENSE = 'DRIVERS_LICENSE';
    case PASSPORT = 'PASSPORT';
    case EID = 'EID';
    case NONE = '';
}
