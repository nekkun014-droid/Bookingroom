<?php
require_once __DIR__ . '/../config/db.php';

class User
{
    public static function findByEmail($email)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function findById($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role_id,created_at) VALUES (?,?,?,?,NOW())');
        return $stmt->execute([$data['name'],$data['email'],$data['password'],$data['role_id']]);
    }
}
