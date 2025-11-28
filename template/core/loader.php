<?php
declare(strict_types=1);

// Absolute base path (template/)
if (!defined('ORIGIN_CORE_PATH')) {
    define('ORIGIN_CORE_PATH', __DIR__ . '/..');
}

// PSR-like autoloader for OriginCore namespace
spl_autoload_register(function (string $class): void {
    $prefix = 'OriginCore\\';
    $baseDir = ORIGIN_CORE_PATH . '/core/';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
