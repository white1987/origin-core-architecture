<?php
declare(strict_types=1);

namespace OriginCore;

final class Rules
{
    public static function allowUniverse(string $universe): bool
    {
        // For now all universes exist logically.
        return true;
    }

    public static function allowPage(string $universe, string $page): bool
    {
        // Example: simple gate for the "admin" universe.
        if ($universe === 'admin') {
            // Very simple demo rule:
            // require ?key=demo in the query string
            return isset($_GET['key']) && $_GET['key'] === 'demo';
        }

        // All other universes are allowed
        return true;
    }
}
