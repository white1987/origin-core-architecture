<?php
// core/universe/env/controller/EnvController.php
declare(strict_types=1);

class EnvController
{
    public static function hello(): void
    {
        echo 'Hello from Env Universe';
    }

    public static function admin(): void
    {
        // controller → env → universe → core → project root
        $page = __DIR__ . '/../../../../pages/env/admin.php';

        if (!file_exists($page)) {
            echo 'Env admin page not ready yet.';
            return;
        }

        require $page;
    }
}
