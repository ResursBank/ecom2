<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Unit\Lib\Validation;

use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Lib\Validation\BoolValidation;

/**
 * Test boolean validation methods.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class BoolValidationTest extends TestCase
{
    /**
     * @var BoolValidation
     */
    private BoolValidation $boolValidation;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->boolValidation = new BoolValidation();

        parent::setUp();
    }

    /**
     * Assert getKey() throws MissingKeyException when the needle does not
     * exist.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithMissing(): void
    {
        $this->expectException(exception: MissingKeyException::class);
        $this->boolValidation->getKey(data: ['Island', 'Green'], key: 'mega');
    }

    /**
     * Assert getKey() throws IllegalTypeException when the needle exists but
     * is not a boolean.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyThrowsWithIllegalType(): void
    {
        $this->expectException(exception: IllegalTypeException::class);
        $this->boolValidation->getKey(data: ['epic' => 1], key: 'epic');
    }

    /**
     * Assert getKey() return validated boolean value.
     *
     * @return void
     * @throws IllegalTypeException
     * @throws MissingKeyException
     */
    public function testGetKeyReturnsBool(): void
    {
        self::assertTrue(
            condition: $this->boolValidation->getKey(
                data: ['epoch' => true],
                key: 'epoch'
            )
        );
    }
}
