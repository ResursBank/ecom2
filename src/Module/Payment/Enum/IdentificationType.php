<?php

namespace Resursbank\Ecom\Module\Payment\Enum;

/**
 * Customer identification types.
 */
enum IdentificationType
{
    case ID;
    case DRIVERS_LICENSE;
    case PASSPORT;
    case EID;
}
