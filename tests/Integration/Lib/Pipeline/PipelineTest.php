<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Pipeline;

use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    public function testCredentialsSet(): void
    {
        print_r($_ENV);
    }
}