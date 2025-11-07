<?php
require_once __DIR__ . '/../models/Timeslot.php';
require_once __DIR__ . '/../middleware/csrf.php';

class TimeslotController
{
    public function index()
    {
        // admin only
        if (empty($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            $_SESSION['flash']['error'] = 'Unauthorized';
            header('Location: ?');
            return;
        }
        $timeslots = Timeslot::all();
        require __DIR__ . '/../views/timeslots/index.php';
    }

    public function exportCsv()
    {
        if (empty($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            $_SESSION['flash']['error'] = 'Unauthorized';
            header('Location: ?');
            return;
        }
        $timeslots = Timeslot::all();
        $filename = 'timeslots_' . date('Ymd_His') . '.csv';
    // log export
    require_once __DIR__ . '/../helpers/logger.php';
    activity_log('export.timeslots', ['count'=>count($timeslots)], $_SESSION['user_id'] ?? null);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output','w');
        fputcsv($out, ['id','name','start_time','end_time','created_at']);
        foreach ($timeslots as $t) {
            fputcsv($out, [$t['id'],$t['name'],$t['start_time'],$t['end_time'],$t['created_at']]);
        }
        fclose($out);
        exit;
    }

    public function store($data)
    {
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=timeslots');
            return;
        }
        if (empty($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            $_SESSION['flash']['error'] = 'Unauthorized';
            header('Location: ?action=timeslots');
            return;
        }
        $name = trim($data['name'] ?? '');
        $start = trim($data['start_time'] ?? '');
        $end = trim($data['end_time'] ?? '');
        if (!$name || !$start || !$end) {
            $_SESSION['flash']['error'] = 'All fields are required.';
            header('Location: ?action=timeslots');
            return;
        }
        // validate ordering
        if ($start >= $end) {
            $_SESSION['flash']['error'] = 'Start time must be before end time.';
            header('Location: ?action=timeslots');
            return;
        }
        // check overlap
        $overlap = Timeslot::findOverlap($start, $end);
        if ($overlap) {
            $_SESSION['flash']['error'] = 'Timeslot overlaps with existing timeslot: ' . ($overlap['name'] ?? '');
            header('Location: ?action=timeslots');
            return;
        }
        $tsId = Timeslot::create(['name'=>$name,'start_time'=>$start,'end_time'=>$end]);
        // log timeslot creation
        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('timeslot.create', ['timeslot_id'=>$tsId,'name'=>$name,'start_time'=>$start,'end_time'=>$end], $_SESSION['user_id'] ?? null);
        $_SESSION['flash']['success'] = 'Timeslot created.';
        header('Location: ?action=timeslots');
    }

    public function delete($data)
    {
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=timeslots');
            return;
        }
        if (empty($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            $_SESSION['flash']['error'] = 'Unauthorized';
            header('Location: ?action=timeslots');
            return;
        }
        $id = (int)($data['id'] ?? 0);
        if ($id) {
            Timeslot::delete($id);
            // log deletion
            require_once __DIR__ . '/../helpers/logger.php';
            activity_log('timeslot.delete', ['timeslot_id'=>$id], $_SESSION['user_id'] ?? null);
            $_SESSION['flash']['success'] = 'Timeslot deleted.';
        }
        header('Location: ?action=timeslots');
    }

    public function update($data)
    {
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=timeslots');
            return;
        }
        if (empty($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            $_SESSION['flash']['error'] = 'Unauthorized';
            header('Location: ?action=timeslots');
            return;
        }
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $start = trim($data['start_time'] ?? '');
        $end = trim($data['end_time'] ?? '');
        if ($id && $name && $start && $end) {
            // validate ordering
            if ($start >= $end) {
                $_SESSION['flash']['error'] = 'Start time must be before end time.';
                header('Location: ?action=timeslots');
                return;
            }
            // check overlap excluding current id
            $overlap = Timeslot::findOverlap($start, $end, $id);
            if ($overlap) {
                $_SESSION['flash']['error'] = 'Timeslot overlaps with existing timeslot: ' . ($overlap['name'] ?? '');
                header('Location: ?action=timeslots');
                return;
            }
            Timeslot::update($id, ['name'=>$name,'start_time'=>$start,'end_time'=>$end]);
            // log update
            require_once __DIR__ . '/../helpers/logger.php';
            activity_log('timeslot.update', ['timeslot_id'=>$id,'name'=>$name,'start_time'=>$start,'end_time'=>$end], $_SESSION['user_id'] ?? null);
            $_SESSION['flash']['success'] = 'Timeslot updated.';
        } else {
            $_SESSION['flash']['error'] = 'Invalid input.';
        }
        header('Location: ?action=timeslots');
    }
}
