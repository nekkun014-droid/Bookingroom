<?php
// Simple helper to clear session and remember-me cookie during development
session_start();
// clear session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
// clear remember_me cookie if present
setcookie('remember_me', '', time() - 3600, '/');
session_destroy();
// redirect back to home
header('Location: /');
exit;
