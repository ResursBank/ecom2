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
 * Ensures that all PHP files have the correct copyright notice after the opening PHP tag.
 */
class CopyrightNoticeSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register(): array
    {
        return [T_OPEN_TAG];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param File $phpcsFile The file where the token was found.
     * @param int $stackPtr The position in the stack where the token was found.
     * @return int|void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // Only process the first open tag
        if ($stackPtr !== 0) {
            return $phpcsFile->numTokens + 1;
        }

        $filename = $phpcsFile->getFilename();
        $content = file_get_contents(filename: $filename);

        if ($content === false) {
            return $phpcsFile->numTokens + 1;
        }

        $lines = explode(separator: "\n", string: $content);

        // Check that we have enough lines
        if (count($lines) < 7) {
            $phpcsFile->addError(
                error: 'Copyright notice is missing after opening PHP tag',
                stackPtr: $stackPtr,
                code: 'Missing'
            );
            return $phpcsFile->numTokens + 1;
        }

        // Line 0: <?php
        if (trim(string: $lines[0]) !== '<?php') {
            return $phpcsFile->numTokens + 1;
        }

        // Line 1: blank line
        if (trim(string: $lines[1]) !== '') {
            $phpcsFile->addError(
                error: 'There must be exactly one blank line after the opening PHP tag',
                stackPtr: $stackPtr,
                code: 'BlankLineAfterOpenTag'
            );
        }

        // Line 2: /**
        if (trim(string: $lines[2]) !== '/**') {
            $phpcsFile->addError(
                error: 'Copyright notice must start with "/**" on line 3',
                stackPtr: $stackPtr,
                code: 'MissingCommentStart'
            );
        }

        // Line 3: * Copyright © Resurs Bank AB. All rights reserved.
        $line3 = trim(string: $lines[3]);
        // Check with the copyright symbol
        if ($line3 !== '* Copyright © Resurs Bank AB. All rights reserved.') {
            $phpcsFile->addError(
                error: 'Line 4 must be " * Copyright © Resurs Bank AB. All rights reserved."',
                stackPtr: $stackPtr,
                code: 'IncorrectCopyrightLine'
            );
        }

        // Line 4: * See LICENSE for license details.
        $line4 = trim(string: $lines[4]);
        if ($line4 !== '* See LICENSE for license details.') {
            $phpcsFile->addError(
                error: 'Line 5 must be " * See LICENSE for license details."',
                stackPtr: $stackPtr,
                code: 'IncorrectLicenseLine'
            );
        }

        // Line 5: */
        if (trim(string: $lines[5]) !== '*/') {
            $phpcsFile->addError(
                error: 'Copyright notice must end with "*/" on line 6',
                stackPtr: $stackPtr,
                code: 'MissingCommentEnd'
            );
        }

        // Line 6: blank line
        if (isset($lines[6]) && trim(string: $lines[6]) !== '') {
            $phpcsFile->addError(
                error: 'There must be exactly one blank line after the copyright notice',
                stackPtr: $stackPtr,
                code: 'BlankLineAfterCopyright'
            );
        }

        return $phpcsFile->numTokens + 1;
    }
}
