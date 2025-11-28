<?php
declare(strict_types=1);

// 1) Load core bootstrapping
require __DIR__ . '/core/loader.php';

// 2) Load configuration
require ORIGIN_CORE_PATH . '/core/config.php';

// 3) Load rules and router
require ORIGIN_CORE_PATH . '/core/rules.php';
require ORIGIN_CORE_PATH . '/core/router.php';

// 4) Dispatch the request
OriginCore\Router::dispatch();
