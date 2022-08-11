<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dir = new DirectoryIterator(directory: __DIR__ . '/Data');

foreach ($dir as $file) {
    if (!$file->isDot() && $file->getExtension() === 'php') {
        require_once $file->getPathname();
    }
}
