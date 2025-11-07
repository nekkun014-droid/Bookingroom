<?php
require_once __DIR__ . '/../config/db.php';

class Timeslot
{
    public static function all()
    {
        $pdo = getPDO();
        $stmt = $pdo->query('SELECT * FROM timeslots ORDER BY start_time');
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM timeslots WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO timeslots (name,start_time,end_time,created_at) VALUES (?,?,?,NOW())');
        return $stmt->execute([$data['name'],$data['start_time'],$data['end_time']]);
    }

    public static function update($id, $data)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE timeslots SET name = ?, start_time = ?, end_time = ? WHERE id = ?');
        return $stmt->execute([$data['name'],$data['start_time'],$data['end_time'],$id]);
    }

    public static function delete($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM timeslots WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Find any timeslot that overlaps the provided time range.
     * If $excludeId is provided, exclude that id (useful for update).
     * Returns the overlapping row or false.
     */
    public static function findOverlap($start_time, $end_time, $excludeId = null)
    {
        $pdo = getPDO();
        $sql = 'SELECT * FROM timeslots WHERE NOT (end_time <= ? OR start_time >= ?)';
        $params = [$start_time, $end_time];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = (int)$excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
