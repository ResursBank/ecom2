<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that doc block summary is only one line.
 */
class DocBlockDescriptionSniff implements Sniff
{
    /**
     * @inheritDoc
     *
     * @return array|int[]|string[]
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @inheritDoc
     *
     * @param File $phpcsFile
     * @param $stackPtr
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $filename = $phpcsFile->getFilename();
        $content = explode(
            separator: "\n",
            string: file_get_contents(filename: $filename)
        );

        $tokens = $phpcsFile->getTokens();
        $numTokens = count($tokens);

        if ($numTokens < 2) {
            return $stackPtr + 1;
        }

        $token = $tokens[$stackPtr];
        if ($token['type'] === 'T_DOC_COMMENT_OPEN_TAG' && $token['line'] !== 3) {
            if (
                !preg_match(
                    pattern: '/^\s*\*\s*@/',
                    subject: $content[$token['line']]
                ) &&
                !preg_match(
                    pattern: '/^\s*\/\*\*\s+@/',
                    subject: $content[$token['line'] - 1]
                )
            ) {
                // Check if next line is empty or end of block
                if (
                    !preg_match(
                        pattern: '/^\s*\*\s*$/',
                        subject: $content[$token['line'] + 1]
                    ) &&
                    !preg_match(
                        pattern: '/^\s*\*\/\s*/',
                        subject: $content[$token['line'] + 1]
                    )
                ) {
                    // Line matches
                    $phpcsFile->addError(
                        error: 'Doc block summary must not be longer than' .
                        ' one line.',
                        stackPtr: $stackPtr,
                        code: 'SingleLineSummary'
                    );
                }
            }
        }
    }
}
