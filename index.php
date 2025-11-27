<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$p = isset($_GET['p']) ? (string)$_GET['p'] : 'home';

route_dispatch($p);
