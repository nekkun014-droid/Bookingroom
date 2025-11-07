<?php
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Timeslot.php';

class RoomController
{
    public function index()
    {
        // read query params
        $q = trim($_GET['q'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 9;
        $total = Room::count($q ?: null);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $rooms = Room::paginate($page, $perPage, $q ?: null);
        // load timeslots for booking modal
        $timeslots = Timeslot::all();
        require __DIR__ . '/../views/rooms/index.php';
    }

    public function exportCsv()
    {
        $q = trim($_GET['q'] ?? '');
        // get all matching rooms (no pagination)
        $rooms = Room::paginate(1, 10000, $q ?: null);
        $filename = 'rooms_' . date('Ymd_His') . '.csv';
    // log export
    require_once __DIR__ . '/../helpers/logger.php';
    activity_log('export.rooms', ['count'=>count($rooms)], $_SESSION['user_id'] ?? null);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','name','location','capacity','created_at']);
        foreach ($rooms as $r) {
            fputcsv($out, [$r['id'],$r['name'],$r['location'],$r['capacity'],$r['created_at']]);
        }
        fclose($out);
        exit;
    }
}
