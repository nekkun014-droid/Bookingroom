<?php
require_once __DIR__ . '/../config/db.php';

class ActivityLog
{
    public static function create($user_id, $action, $details = null, $ip = null, $user_agent = null)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip, user_agent, created_at) VALUES (?,?,?,?,?,NOW())');
        return $stmt->execute([$user_id, $action, $details, $ip, $user_agent]);
    }
}
