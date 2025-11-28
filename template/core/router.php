<?php
declare(strict_types=1);

namespace OriginCore;

use OriginCore\Config;
use OriginCore\Rules;

final class Router
{
    public static function dispatch(): void
    {
        $universe = isset($_GET['u'])
            ? self::sanitize((string)$_GET['u'])
            : Config::DEFAULT_UNIVERSE;

        $page = isset($_GET['p'])
            ? self::sanitize((string)$_GET['p'])
            : Config::DEFAULT_PAGE;

        if (!Rules::allowUniverse($universe) || !Rules::allowPage($universe, $page)) {
            self::renderForbidden();
            return;
        }

        $file = Config::universesPath() . "/{$universe}/pages/{$page}.php";

        if (!is_file($file)) {
            self::renderNotFound($universe, $page);
            return;
        }

        // shared helpers + layout
        require_once Config::sharedPath() . '/helpers/html.php';
        require_once Config::sharedPath() . '/ui/layout.php';

        $ctx = ['universe' => $universe, 'page' => $page];
        extract($ctx, EXTR_SKIP);

        require $file;
    }

    private static function sanitize(string $s): string
    {
        $s = strtolower($s);
        // allow sub-pages like "auth/login"
        $s = preg_replace('#[^a-z0-9/_-]#', '', $s);
        $s = preg_replace('#/+#', '/', $s);
        $s = trim($s, '/');
        return $s === '' ? 'index' : $s;
    }

    private static function renderNotFound(string $universe, string $page): void
    {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>Universe: <code>" . htmlspecialchars($universe, ENT_QUOTES, 'UTF-8') . "</code></p>";
        echo "<p>Page: <code>" . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . "</code></p>";
    }

    private static function renderForbidden(): void
    {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1>";
        echo "<p>Access denied by Origin-Core rules.</p>";
    }
}
