<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco;

use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Rco\Customer\Type;
use Resursbank\Ecom\Lib\Model\Rco\Enum\CountryCode;

/**
 * Implementation of CustomerDto object.
 */
class Customer extends Model
{
    /**
     * @param Type $type Customer type enum.
     * @param string|null $governmentId Government id supplied by the customer.
     * @param Recipient|null $billing Billing address object.
     * @param Recipient|null $delivery Delivery address object.
     */
    public function __construct(
        public readonly Type $type,
        #[StringMatchesRegex(
            // Temporary fix for masked government id.
            pattern: '/^$|^(?:SE|FI|DK|NO)[-+A-Za-z0-9\*]{6,18}$/'
            //pattern: '/^$|^(?:SE|FI|DK|NO)[-+A-Za-z0-9]{6,18}$/'
        )]
        public readonly ?string $governmentId = null,
        public readonly ?Recipient $billing = null,
        public readonly ?Recipient $delivery = null
    ) {
        parent::__construct();
    }

    /**
     * This logic is copied from the API to confirm if there is a separate
     * delivery address. In a nutshell, if there is a value for any property
     * associated with the delivery address, then there is a delivery address.
     *
     * @return bool
     */
    public function useSeparateDeliveryAddress(): bool
    {
        return
            $this->delivery?->address?->addressLine ||
            $this->delivery?->address?->city ||
            $this->delivery?->address?->countryCode !== CountryCode::UNKNOWN ||
            $this->delivery?->address?->postalCode ||
            $this->delivery?->address?->street ||
            $this->delivery?->contact?->phone ||
            $this->delivery?->contact?->email ||
            $this->delivery?->contact?->firstName ||
            $this->delivery?->contact?->lastName ||
            $this->delivery?->name;
    }
}
