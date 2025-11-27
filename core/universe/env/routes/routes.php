<?php
// core/universe/env/routes/routes.php
declare(strict_types=1);

return [
    'env_hello' => [
        'handler' => 'EnvController@hello',
    ],
    'env_admin' => [
        'handler' => 'EnvController@admin',
    ],
];
