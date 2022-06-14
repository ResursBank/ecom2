<?php

namespace Resursbank\Ecom\Lib\Model;

/**
 * Defines the basic structure of an Ecom model
 */
class Model
{
    private array $data;

    public function __construct()
    {
    }

    public function save(): bool
    {
        // @todo Implement saving
    }

    public function load(int $id): self
    {
        // @todo Implement loading
        return $this;
    }
}
