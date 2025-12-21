<?php
require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../models/Room.php';

class ApiRoomController extends ApiController
{
    public function index()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        $rooms = Room::paginate($page, $perPage, $search);
        $total = Room::count($search);
        $totalPages = ceil($total / $perPage);

        $this->success([
            'rooms' => $rooms,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }

    public function show($id)
    {
        $room = Room::find($id);
        
        if (!$room) {
            $this->notFound('Room not found');
        }

        $this->success(['room' => $room]);
    }

    public function store()
    {
        $user = $this->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateRequired($data, ['name', 'location', 'capacity']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $capacity = (int)$data['capacity'];
        if ($capacity <= 0) {
            $this->error('Capacity must be greater than 0', 400);
        }

        $success = Room::create([
            'name' => trim($data['name']),
            'location' => trim($data['location']),
            'capacity' => $capacity
        ]);

        if (!$success) {
            $this->error('Failed to create room', 500);
        }

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.room.create', ['name' => $data['name']], $user['id']);

        $this->success([], 'Room created successfully', 201);
    }
}
