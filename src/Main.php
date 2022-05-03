<?php

declare(strict_types=1);

namespace Resursbank;

use Resursbank\Ecom\Api\Credentials;
use Resursbank\Ecom\Locale\Country;
use Resursbank\Ecom\Log\LoggerInterface;
use Resursbank\Ecom\Simplified\Config;

/**
 * API communication object.
 */
class Main
{
    /**
     * @param Credentials $credentials
     * @param LoggerInterface $logger
     * @param Country $country
     * @param Config $simplified
     */
    public function __construct(
        public readonly Credentials $credentials,
        public readonly LoggerInterface $logger,
        public readonly Country $country,
        public readonly Config $simplified
    ) {
    }
}
