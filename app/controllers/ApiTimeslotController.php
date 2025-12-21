<?php
require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../models/Timeslot.php';

class ApiTimeslotController extends ApiController
{
    public function index()
    {
        $timeslots = Timeslot::all();
        $this->success(['timeslots' => $timeslots]);
    }

    public function show($id)
    {
        $timeslot = Timeslot::find($id);
        
        if (!$timeslot) {
            $this->notFound('Timeslot not found');
        }

        $this->success(['timeslot' => $timeslot]);
    }

    public function store()
    {
        $user = $this->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateRequired($data, ['name', 'start_time', 'end_time']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $start_time = trim($data['start_time']);
        $end_time = trim($data['end_time']);

        // Validate time format (HH:MM:SS)
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $start_time) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $end_time)) {
            $this->error('Invalid time format. Use: HH:MM:SS', 400);
        }

        if ($start_time >= $end_time) {
            $this->error('Start time must be before end time', 400);
        }

        // Check for overlaps
        $overlap = Timeslot::findOverlap($start_time, $end_time);
        if ($overlap) {
            $this->error('Timeslot overlaps with existing timeslot: ' . $overlap['name'], 409);
        }

        $success = Timeslot::create([
            'name' => trim($data['name']),
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);

        if (!$success) {
            $this->error('Failed to create timeslot', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.timeslot.create', ['name' => $data['name']], $user['id']);

        $this->success([], 'Timeslot created successfully', 201);
    }

    public function update($id)
    {
        $user = $this->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $timeslot = Timeslot::find($id);
        if (!$timeslot) {
            $this->notFound('Timeslot not found');
        }

        $errors = $this->validateRequired($data, ['name', 'start_time', 'end_time']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $start_time = trim($data['start_time']);
        $end_time = trim($data['end_time']);

        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $start_time) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $end_time)) {
            $this->error('Invalid time format. Use: HH:MM:SS', 400);
        }

        if ($start_time >= $end_time) {
            $this->error('Start time must be before end time', 400);
        }

        // Check for overlaps (excluding current timeslot)
        $overlap = Timeslot::findOverlap($start_time, $end_time, $id);
        if ($overlap) {
            $this->error('Timeslot overlaps with existing timeslot: ' . $overlap['name'], 409);
        }

        $success = Timeslot::update($id, [
            'name' => trim($data['name']),
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);

        if (!$success) {
            $this->error('Failed to update timeslot', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.timeslot.update', ['timeslot_id' => $id], $user['id']);

        $this->success([], 'Timeslot updated successfully');
    }

    public function destroy($id)
    {
        $user = $this->requireAdmin();
        
        $timeslot = Timeslot::find($id);
        if (!$timeslot) {
            $this->notFound('Timeslot not found');
        }

        $success = Timeslot::delete($id);

        if (!$success) {
            $this->error('Failed to delete timeslot', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.timeslot.delete', ['timeslot_id' => $id], $user['id']);

        $this->success([], 'Timeslot deleted successfully');
    }
}
