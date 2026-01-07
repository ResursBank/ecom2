# Resursbank Custom PHPCS Sniffs

This directory contains custom PHP_CodeSniffer sniffs for Resursbank coding standards.

## Copyright Notice Sniffs

### CopyrightNoticeSniff

Ensures that all PHP files have the correct copyright notice immediately after the opening PHP tag with exactly one blank line in between.

**Expected format:**
```php
<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */
```

**Rules:**
- Must have exactly one blank line after `<?php`
- Copyright notice must be a doc comment block
- Copyright text must match exactly
