<?php
// core/i18n.php
declare(strict_types=1);

/**
 * Minimal stub for future i18n.
 * For now it simply returns the fallback text.
 * Later you can load JSON files and perform real translations here.
 */
function T(string $key, string $fallback): string
{
    return $fallback;
}
