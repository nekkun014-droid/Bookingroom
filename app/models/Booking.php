<?php
require_once __DIR__ . '/../config/db.php';

class Booking
{
    public static function create($data)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, room_id, start_time, end_time, status, created_at) VALUES (?,?,?,?,?,NOW())');
        return $stmt->execute([$data['user_id'],$data['room_id'],$data['start_time'],$data['end_time'],$data['status']]);
    }

    public static function findConflicts($room_id, $start_time, $end_time)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE room_id = ? AND status = "approved" AND NOT (end_time <= ? OR start_time >= ?) LIMIT 1');
        $stmt->execute([$room_id, $start_time, $end_time]);
        return $stmt->fetch();
    }

    public static function allByUser($user_id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT b.*, r.name AS room_name FROM bookings b JOIN rooms r ON r.id = b.room_id WHERE b.user_id = ? ORDER BY b.start_time DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public static function all()
    {
        $pdo = getPDO();
        $stmt = $pdo->query('SELECT b.*, r.name AS room_name, u.name AS user_name FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN users u ON u.id = b.user_id ORDER BY b.start_time DESC');
        return $stmt->fetchAll();
    }

    public static function findById($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT b.*, r.name AS room_name, r.id as room_id, u.name AS user_name FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function updateStatus($id, $status)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }
}
