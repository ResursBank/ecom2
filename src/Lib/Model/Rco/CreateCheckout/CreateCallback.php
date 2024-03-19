<?php

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Rco\CreateCheckout;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of CreateCallbackDto object.
 */
class CreateCallback extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^https?:\/\/[-a-zA-Z0-9+&@…*[-a-zA-Z0-9+&@#\/%=~_|]/'
        )]
        public readonly string $url
    ) {
        parent::__construct();
    }
}
