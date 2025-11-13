  <?php
// Seed demo users and rooms with password_hash. Run from CLI or browser (once).
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/db.php';
$pdo = getPDO();

// demo users
$users = [
    ['name'=>'Admin','email'=>'admin@example.com','password'=>'Admin@123','role_id'=>1],
    ['name'=>'User','email'=>'user@example.com','password'=>'User@123','role_id'=>2]
];
foreach ($users as $u) {
    // check exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$u['email']]);
    if ($stmt->fetch()) continue;
    $pwd = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role_id,created_at) VALUES (?,?,?,?,NOW())');
    $stmt->execute([$u['name'],$u['email'],$pwd,$u['role_id']]);
}

// sample rooms
$rooms = [
    ['Lab A','Building 1',20],
    ['Lab B','Building 2',15],
    ['Meeting Room','Building 1',8]
];
foreach ($rooms as $r) {
    $stmt = $pdo->prepare('SELECT id FROM rooms WHERE name = ?');
    $stmt->execute([$r[0]]);
    if ($stmt->fetch()) continue;
    $stmt = $pdo->prepare('INSERT INTO rooms (name,location,capacity,created_at) VALUES (?,?,?,NOW())');
    $stmt->execute([$r[0],$r[1],$r[2]]);
}

// sample timeslots
$times = [
    ['Morning','08:00:00','10:00:00'],
    ['Late Morning','10:00:00','12:00:00'],
    ['Afternoon','13:00:00','15:00:00']
];
foreach ($times as $t) {
    $stmt = $pdo->prepare('SELECT id FROM timeslots WHERE name = ?');
    $stmt->execute([$t[0]]);
    if ($stmt->fetch()) continue;
    $stmt = $pdo->prepare('INSERT INTO timeslots (name,start_time,end_time,created_at) VALUES (?,?,?,NOW())');
    $stmt->execute([$t[0],$t[1],$t[2]]);
}

echo "Seeding completed.\n";
