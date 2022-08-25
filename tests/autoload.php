<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest;

use DirectoryIterator;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Load Mock data classes.
 */
final class Loader
{
    public const DATA_DIR = __DIR__ . '/Data';

    /**
     * Load everything in data directory recursively.
     *
     * @param string $path
     * @return void
     */
    public static function load(
        string $path
    ): void {
        $dir = new DirectoryIterator(directory: $path);

        foreach ($dir as $file) {
            if (!$file->isDot()) {
                if ($file->isDir()) {
                    self::load(path: $file->getPathname());
                } elseif ($file->getExtension() === 'php') {
                    require_once $file->getPathname();
                }
            }
        }
    }
}

Loader::load(path: Loader::DATA_DIR);
