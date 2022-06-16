<?php

namespace Resursbank\Ecom\Lib\Network;

class RequestMethod
{
    /**
     * HTTP GET Method (default).
     * @var int
     */
    public const GET = 0;
    /**
     * HTTP POST Method.
     * @var int
     */
    public const POST = 1;
    /**
     * HTTP PUT Method.
     * @var int
     */
    public const PUT = 2;
    /**
     * HTTP DELETE Method.
     * @var int
     */
    public const DELETE = 3;
    /**
     * HTTP HEAD Method.
     * @var int
     */
    public const HEAD = 4;
    /**
     * HTTP REQUEST Method.
     * @var int
     */
    public const REQUEST = 5;
    /**
     * HTTP PATCH Method.
     * @var int
     */
    public const PATCH = 6;
}
