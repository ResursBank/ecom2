<?php

namespace Resursbank\Ecom\Lib\Network;

class DataType
{
    /**
     * Default DataType means that we usually use the standard GET/POST variables like ?var=val&var1=val1
     * @var int
     */
    public const POSTVARS = 0;

    /**
     * Using JSON-formatted data.
     * @var int
     */
    public const JSON = 1;
}
