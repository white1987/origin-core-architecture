<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'universe' => $universe,
    'page'     => $page,
    'status'   => 'ok',
    'time'     => time(),
]);
