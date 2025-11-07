<?php
require_once __DIR__ . '/../models/ActivityLog.php';

function activity_log($action, $details = null, $user_id = null)
{
    // determine user id if not provided
    if (!$user_id) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    // details: normalize to string
    if (is_array($details) || is_object($details)) {
        $details = json_encode($details, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
    ActivityLog::create($user_id, $action, $details, $ip, $ua);
}
