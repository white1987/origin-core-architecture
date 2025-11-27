<?php
// core/universe/user/routes/routes.php
declare(strict_types=1);

return [
    'user_hello' => [
        'handler' => 'UserController@hello',
    ],
    'user_login' => [
        'handler' => 'UserController@login',
    ],
];
