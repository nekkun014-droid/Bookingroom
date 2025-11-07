<?php
require_once __DIR__ . '/../config/constants.php';

function csrf_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function validate_csrf($token)
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return hash_equals($_SESSION[CSRF_TOKEN_KEY] ?? '', $token ?? '');
}
