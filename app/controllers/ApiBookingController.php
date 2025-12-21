<?php
require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';

class ApiBookingController extends ApiController
{
    public function index()
    {
        $user = $this->requireAuth();
        
        // Admin can see all bookings, regular users see only their own
        if ($user['role_id'] == 1) {
            $bookings = Booking::all();
        } else {
            $bookings = Booking::allByUser($user['id']);
        }

        $this->success(['bookings' => $bookings]);
    }

    public function show($id)
    {
        $user = $this->requireAuth();
        
        $booking = Booking::findById($id);
        
        if (!$booking) {
            $this->notFound('Booking not found');
        }

        // Regular users can only view their own bookings
        if ($user['role_id'] != 1 && $booking['user_id'] != $user['id']) {
            $this->forbidden('You can only view your own bookings');
        }

        $this->success(['booking' => $booking]);
    }

    public function store()
    {
        $user = $this->requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateRequired($data, ['room_id', 'start_time', 'end_time']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $room_id = (int)$data['room_id'];
        $room = Room::find($room_id);
        
        if (!$room) {
            $this->notFound('Room not found');
        }

        $start_time = trim($data['start_time']);
        $end_time = trim($data['end_time']);

        // Validate datetime format
        if (!strtotime($start_time) || !strtotime($end_time)) {
            $this->error('Invalid datetime format. Use: YYYY-MM-DD HH:MM:SS', 400);
        }

        if (strtotime($start_time) >= strtotime($end_time)) {
            $this->error('Start time must be before end time', 400);
        }

        // Check for conflicts
        $conflict = Booking::findConflicts($room_id, $start_time, $end_time);
        if ($conflict) {
            $this->error('Room already booked for the selected time', 409);
        }

        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, room_id, start_time, end_time, status, created_at) VALUES (?,?,?,?,?,NOW())');
        $success = $stmt->execute([$user['id'], $room_id, $start_time, $end_time, 'pending']);

        if (!$success) {
            $this->error('Failed to create booking', 500);
        }

        $bookingId = $pdo->lastInsertId();

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.booking.create', [
            'booking_id' => $bookingId,
            'room_id' => $room_id,
            'start_time' => $start_time,
            'end_time' => $end_time
        ], $user['id']);

        $this->success([
            'booking_id' => $bookingId
        ], 'Booking created successfully', 201);
    }

    public function updateStatus($id)
    {
        $user = $this->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['status']) || !in_array($data['status'], ['approved', 'rejected', 'pending'])) {
            $this->error('Invalid status. Must be: approved, rejected, or pending', 400);
        }

        $booking = Booking::findById($id);
        
        if (!$booking) {
            $this->notFound('Booking not found');
        }

        // If approving, check for conflicts
        if ($data['status'] === 'approved') {
            $conflict = Booking::findConflicts($booking['room_id'], $booking['start_time'], $booking['end_time']);
            if ($conflict && $conflict['id'] != $id) {
                $this->error('Cannot approve: conflicting approved booking exists', 409);
            }
        }

        $success = Booking::updateStatus($id, $data['status']);

        if (!$success) {
            $this->error('Failed to update booking status', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.booking.update_status', [
            'booking_id' => $id,
            'status' => $data['status']
        ], $user['id']);

        $this->success([], 'Booking status updated successfully');
    }

    public function destroy($id)
    {
        $user = $this->requireAuth();
        
        $booking = Booking::findById($id);
        
        if (!$booking) {
            $this->notFound('Booking not found');
        }

        // Only admin or booking owner can delete
        if ($user['role_id'] != 1 && $booking['user_id'] != $user['id']) {
            $this->forbidden('You can only delete your own bookings');
        }

        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
        $success = $stmt->execute([$id]);

        if (!$success) {
            $this->error('Failed to delete booking', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.booking.delete', ['booking_id' => $id], $user['id']);

        $this->success([], 'Booking deleted successfully');
    }
}
