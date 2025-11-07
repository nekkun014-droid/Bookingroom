<?php
require_once __DIR__ . '/../config/db.php';

class PersistentLogin
{
    public static function create($user_id, $selector, $token_hash, $expires_at)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO persistent_logins (user_id, selector, token_hash, expires_at, created_at) VALUES (?,?,?,?,NOW())');
        return $stmt->execute([$user_id, $selector, $token_hash, $expires_at]);
    }

    public static function findBySelector($selector)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM persistent_logins WHERE selector = ? LIMIT 1');
        $stmt->execute([$selector]);
        return $stmt->fetch();
    }

    public static function deleteBySelector($selector)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM persistent_logins WHERE selector = ?');
        return $stmt->execute([$selector]);
    }

    public static function deleteByUser($user_id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM persistent_logins WHERE user_id = ?');
        return $stmt->execute([$user_id]);
    }

    public static function updateToken($selector, $newTokenHash, $newExpiry)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE persistent_logins SET token_hash = ?, expires_at = ? WHERE selector = ?');
        return $stmt->execute([$newTokenHash, $newExpiry, $selector]);
    }
}
