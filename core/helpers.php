<?php
// core/helpers.php
declare(strict_types=1);

/**
 * Simple HTML escape helper.
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
