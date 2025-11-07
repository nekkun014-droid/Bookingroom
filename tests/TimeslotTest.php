<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class TimeslotTest extends TestCase
{
    protected $pdo;

    protected function setUp(): void
    {
        $this->pdo = getPDO();
        // clean tables
        $this->pdo->exec('DELETE FROM timeslots');
    }

    public function testFindOverlapDetectsOverlap()
    {
        // insert a timeslot 08:00-10:00
        $stmt = $this->pdo->prepare('INSERT INTO timeslots (name,start_time,end_time,created_at) VALUES (?,?,?,?)');
        $stmt->execute(['Morning','08:00:00','10:00:00',date('Y-m-d H:i:s')]);

        $over = Timeslot::findOverlap('09:00:00','11:00:00');
        $this->assertIsArray($over);
        $this->assertEquals('Morning', $over['name']);
    }

    public function testFindOverlapExcludesSelf()
    {
        // insert two timeslots
        $stmt = $this->pdo->prepare('INSERT INTO timeslots (name,start_time,end_time,created_at) VALUES (?,?,?,?)');
        $stmt->execute(['A','08:00:00','10:00:00',date('Y-m-d H:i:s')]);
        $idA = $this->pdo->lastInsertId();
        $stmt->execute(['B','10:30:00','12:00:00',date('Y-m-d H:i:s')]);
        // overlapping with A
        $over = Timeslot::findOverlap('09:00:00','11:00:00', $idA);
        // since we excluded A, overlap should be false because B does not overlap
        $this->assertFalse($over);
    }
}
