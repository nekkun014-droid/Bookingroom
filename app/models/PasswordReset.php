<?php
require_once __DIR__ . '/../config/db.php';

class PasswordReset
{
    public static function create($email, $token_hash, $expires_at)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO password_resets (email, token_hash, expires_at, created_at) VALUES (?,?,?,NOW())');
        return $stmt->execute([$email, $token_hash, $expires_at]);
    }

    public static function findByToken($token)
    {
        $pdo = getPDO();
        $token_hash = hash('sha256', $token);
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND expires_at >= NOW() LIMIT 1');
        $stmt->execute([$token_hash]);
        return $stmt->fetch();
    }

    public static function deleteByEmail($email)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        return $stmt->execute([$email]);
    }

    public static function deleteByToken($token)
    {
        $pdo = getPDO();
        $token_hash = hash('sha256', $token);
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token_hash = ?');
        return $stmt->execute([$token_hash]);
    }
}
