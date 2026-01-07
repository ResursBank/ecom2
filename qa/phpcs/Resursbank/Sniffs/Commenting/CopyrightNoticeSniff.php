<?php

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
        $content = file_get_contents($filename);

        if ($content === false) {
            return $phpcsFile->numTokens + 1;
        }

        $lines = explode("\n", $content);

        // Check that we have enough lines
        if (count($lines) < 7) {
            $phpcsFile->addError(
                'Copyright notice is missing after opening PHP tag',
                $stackPtr,
                'Missing'
            );
            return $phpcsFile->numTokens + 1;
        }

        // Line 0: <?php
        if (trim($lines[0]) !== '<?php') {
            // This shouldn't happen if we're at T_OPEN_TAG, but let's check anyway
            return $phpcsFile->numTokens + 1;
        }

        // Line 1: blank line
        if (trim($lines[1]) !== '') {
            $phpcsFile->addError(
                'There must be exactly one blank line after the opening PHP tag',
                $stackPtr,
                'BlankLineAfterOpenTag'
            );
        }

        // Line 2: /**
        if (trim($lines[2]) !== '/**') {
            $phpcsFile->addError(
                'Copyright notice must start with "/**" on line 3',
                $stackPtr,
                'MissingCommentStart'
            );
        }

        // Line 3: * Copyright © Resurs Bank AB. All rights reserved.
        $line3 = trim($lines[3]);
        // Check with the copyright symbol
        if ($line3 !== '* Copyright © Resurs Bank AB. All rights reserved.') {
            $phpcsFile->addError(
                'Line 4 must be " * Copyright © Resurs Bank AB. All rights reserved."',
                $stackPtr,
                'IncorrectCopyrightLine'
            );
        }

        // Line 4: * See LICENSE for license details.
        $line4 = trim($lines[4]);
        if ($line4 !== '* See LICENSE for license details.') {
            $phpcsFile->addError(
                'Line 5 must be " * See LICENSE for license details."',
                $stackPtr,
                'IncorrectLicenseLine'
            );
        }

        // Line 5: */
        if (trim($lines[5]) !== '*/') {
            $phpcsFile->addError(
                'Copyright notice must end with "*/" on line 6',
                $stackPtr,
                'MissingCommentEnd'
            );
        }

        // Line 6: blank line
        if (isset($lines[6]) && trim($lines[6]) !== '') {
            $phpcsFile->addError(
                'There must be exactly one blank line after the copyright notice',
                $stackPtr,
                'BlankLineAfterCopyright'
            );
        }

        return $phpcsFile->numTokens + 1;
    }
}
