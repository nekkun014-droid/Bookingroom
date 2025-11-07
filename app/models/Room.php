<?php
require_once __DIR__ . '/../config/db.php';

class Room
{
    public static function all($limit = 50, $offset = 0)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM rooms ORDER BY name LIMIT ? OFFSET ?');
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Paginate rooms with optional search query.
     * @param int $page 1-based page
     * @param int $perPage
     * @param string|null $search
     * @return array
     */
    public static function paginate($page = 1, $perPage = 9, $search = null)
    {
        $offset = max(0, ($page - 1) * $perPage);
        $pdo = getPDO();
        if ($search) {
            $q = '%' . $search . '%';
            $stmt = $pdo->prepare('SELECT * FROM rooms WHERE name LIKE ? OR location LIKE ? ORDER BY name LIMIT ? OFFSET ?');
            $stmt->bindValue(1, $q, PDO::PARAM_STR);
            $stmt->bindValue(2, $q, PDO::PARAM_STR);
            $stmt->bindValue(3, (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(4, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $stmt = $pdo->prepare('SELECT * FROM rooms ORDER BY name LIMIT ? OFFSET ?');
        $stmt->bindValue(1, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count($search = null)
    {
        $pdo = getPDO();
        if ($search) {
            $q = '%' . $search . '%';
            $stmt = $pdo->prepare('SELECT COUNT(*) as c FROM rooms WHERE name LIKE ? OR location LIKE ?');
            $stmt->execute([$q, $q]);
            $row = $stmt->fetch();
            return (int)($row['c'] ?? 0);
        }
        $stmt = $pdo->query('SELECT COUNT(*) as c FROM rooms');
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function find($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO rooms (name, location, capacity, created_at) VALUES (?,?,?,NOW())');
        return $stmt->execute([$data['name'],$data['location'],$data['capacity']]);
    }
}
