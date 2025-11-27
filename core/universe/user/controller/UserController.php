<?php
// core/universe/user/controller/UserController.php
declare(strict_types=1);

class UserController
{
    public static function hello(): void
    {
        echo 'Hello from User Universe';
    }

    public static function login(): void
    {
        // controller → user → universe → core → project root
        $page = __DIR__ . '/../../../../pages/user/login.php';

        if (!file_exists($page)) {
            echo 'Login page not ready yet.';
            return;
        }

        require $page;
    }
}
