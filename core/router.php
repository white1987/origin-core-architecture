<?php
// core/router.php
declare(strict_types=1);

/**
 * Load routes from all universes.
 * No in-memory cache: every request sees latest routes.php changes.
 */
function origin_routes(): array
{
    $universeRoutes = [];
    $universeRoot = __DIR__ . '/universe';

    $universeDirs = glob($universeRoot . '/*', GLOB_ONLYDIR) ?: [];

    foreach ($universeDirs as $uDir) {
        $routeFile = $uDir . '/routes/routes.php';

        if (file_exists($routeFile)) {
            $uName = basename($uDir);
            $routes = require $routeFile;

            if (is_array($routes)) {
                $universeRoutes[$uName] = $routes;
            }
        }
    }

    return $universeRoutes;
}

/**
 * Dispatch a request based on the "p" query parameter.
 */
function route_dispatch(string $p): void
{
    $routes = origin_routes();

    foreach ($routes as $uName => $uRoutes) {
        if (!isset($uRoutes[$p])) {
            continue;
        }

        $info = $uRoutes[$p];
        if (!isset($info['handler'])) {
            continue;
        }

        // handler format: "UserController@login"
        [$cls, $method] = explode('@', $info['handler']);

        $controllerFile = __DIR__ . "/universe/{$uName}/controller/{$cls}.php";
        if (!file_exists($controllerFile)) {
            echo "Controller file not found: {$controllerFile}";
            return;
        }

        require_once $controllerFile;

        if (!class_exists($cls)) {
            echo "Controller class not found: {$cls}";
            return;
        }

        if (!is_callable([$cls, $method])) {
            echo "Controller method not callable: {$cls}@{$method}";
            return;
        }

        $cls::$method();
        return;
    }

    // Fallback: home page
    require __DIR__ . '/../pages/home.php';
}
