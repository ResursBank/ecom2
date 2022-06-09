<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Rco\Models;

class Payment
{
    public string $id;
    public float $totalAmount;
    public MetaDataCollection $metaData;
    public float $limit;
    
}
