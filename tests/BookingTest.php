<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class BookingTest extends TestCase
{
    protected $pdo;

    protected function setUp(): void
    {
        $this->pdo = getPDO();
        $this->pdo->exec('DELETE FROM bookings');
        $this->pdo->exec('DELETE FROM users');
        $this->pdo->exec('DELETE FROM rooms');
    }

    public function testFindConflictsFindsApproved()
    {
        // create user
        $stmt = $this->pdo->prepare('INSERT INTO users (name,email,password,role_id,created_at) VALUES (?,?,?,?,?)');
        $stmt->execute(['U','u@example.test','x',2,date('Y-m-d H:i:s')]);
        $userId = $this->pdo->lastInsertId();
        // create room
        $stmt = $this->pdo->prepare('INSERT INTO rooms (name,location,capacity,created_at) VALUES (?,?,?,?)');
        $stmt->execute(['R','L',10,date('Y-m-d H:i:s')]);
        $roomId = $this->pdo->lastInsertId();
        // create approved booking 09:00-11:00
        $stmt = $this->pdo->prepare('INSERT INTO bookings (user_id,room_id,start_time,end_time,status,created_at) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$userId,$roomId,'09:00:00','11:00:00','approved',date('Y-m-d H:i:s')]);

        $conflict = Booking::findConflicts($roomId, '10:00:00', '12:00:00');
        $this->assertIsArray($conflict);
        $this->assertEquals('approved', $conflict['status']);
    }

    public function testFindConflictsIgnoresPending()
    {
        // create user and room
        $stmt = $this->pdo->prepare('INSERT INTO users (name,email,password,role_id,created_at) VALUES (?,?,?,?,?)');
        $stmt->execute(['U','u2@example.test','x',2,date('Y-m-d H:i:s')]);
        $userId = $this->pdo->lastInsertId();
        $stmt = $this->pdo->prepare('INSERT INTO rooms (name,location,capacity,created_at) VALUES (?,?,?,?)');
        $stmt->execute(['R2','L2',5,date('Y-m-d H:i:s')]);
        $roomId = $this->pdo->lastInsertId();
        // create pending booking overlapping
        $stmt = $this->pdo->prepare('INSERT INTO bookings (user_id,room_id,start_time,end_time,status,created_at) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$userId,$roomId,'09:00:00','11:00:00','pending',date('Y-m-d H:i:s')]);

        $conflict = Booking::findConflicts($roomId, '09:30:00', '10:30:00');
        $this->assertFalse($conflict);
    }
}
