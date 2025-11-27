<?php
// core/session.php
declare(strict_types=1);

/**
 * Minimal session bootstrap.
 * You can extend this with your own session strategies.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
