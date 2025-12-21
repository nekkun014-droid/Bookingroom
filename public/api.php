<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/db.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/helpers/'
    ];
    foreach ($paths as $p) {
        $file = $p . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

function sendError($message, $code = 400)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Parse request URI
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove script name from path
$scriptName = $_SERVER['SCRIPT_NAME'];
if (strpos($path, $scriptName) === 0) {
    $path = substr($path, strlen($scriptName));
}

// Remove /api prefix if present
$path = preg_replace('#^/api/?#', '', $path);
$path = trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];
$segments = array_filter(explode('/', $path));
$segments = array_values($segments);

// Routing
try {
    // Auth routes (no authentication required)
    if (isset($segments[0]) && $segments[0] === 'auth') {
        $controller = new ApiAuthController();
        
        if ($method === 'POST' && isset($segments[1])) {
            if ($segments[1] === 'register') {
                $controller->register();
            } elseif ($segments[1] === 'login') {
                $controller->login();
            }
        } elseif ($method === 'GET' && isset($segments[1]) && $segments[1] === 'me') {
            $controller->me();
        }
        
        sendError('Route not found', 404);
    }

    // Rooms routes
    if (isset($segments[0]) && $segments[0] === 'rooms') {
        $controller = new ApiRoomController();
        
        // GET /rooms - List all rooms
        if ($method === 'GET' && count($segments) === 1) {
            $controller->index();
        }
        
        // GET /rooms/{id} - Get single room
        if ($method === 'GET' && count($segments) === 2) {
            $controller->show((int)$segments[1]);
        }
        
        // POST /rooms - Create new room (admin only)
        if ($method === 'POST' && count($segments) === 1) {
            $controller->store();
        }
        
        sendError('Route not found', 404);
    }

    // Bookings routes
    if (isset($segments[0]) && $segments[0] === 'bookings') {
        $controller = new ApiBookingController();
        
        // GET /bookings - List bookings
        if ($method === 'GET' && count($segments) === 1) {
            $controller->index();
        }
        
        // GET /bookings/{id} - Get single booking
        if ($method === 'GET' && count($segments) === 2) {
            $controller->show((int)$segments[1]);
        }
        
        // POST /bookings - Create new booking
        if ($method === 'POST' && count($segments) === 1) {
            $controller->store();
        }
        
        // PUT /bookings/{id}/status - Update booking status (admin only)
        if ($method === 'PUT' && count($segments) === 3 && $segments[2] === 'status') {
            $controller->updateStatus((int)$segments[1]);
        }
        
        // DELETE /bookings/{id} - Delete booking
        if ($method === 'DELETE' && count($segments) === 2) {
            $controller->destroy((int)$segments[1]);
        }
        
        sendError('Route not found', 404);
    }

    // Timeslots routes
    if (isset($segments[0]) && $segments[0] === 'timeslots') {
        $controller = new ApiTimeslotController();
        
        // GET /timeslots - List all timeslots
        if ($method === 'GET' && count($segments) === 1) {
            $controller->index();
        }
        
        // GET /timeslots/{id} - Get single timeslot
        if ($method === 'GET' && count($segments) === 2) {
            $controller->show((int)$segments[1]);
        }
        
        // POST /timeslots - Create new timeslot (admin only)
        if ($method === 'POST' && count($segments) === 1) {
            $controller->store();
        }
        
        // PUT /timeslots/{id} - Update timeslot (admin only)
        if ($method === 'PUT' && count($segments) === 2) {
            $controller->update((int)$segments[1]);
        }
        
        // DELETE /timeslots/{id} - Delete timeslot (admin only)
        if ($method === 'DELETE' && count($segments) === 2) {
            $controller->destroy((int)$segments[1]);
        }
        
        sendError('Route not found', 404);
    }

    // Root endpoint
    if (empty($segments[0])) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Booking Room API',
            'version' => '1.0.0',
            'endpoints' => [
                'POST /auth/register' => 'Register new user',
                'POST /auth/login' => 'Login user',
                'GET /auth/me' => 'Get current user (requires token)',
                'GET /rooms' => 'List all rooms',
                'GET /rooms/{id}' => 'Get room details',
                'POST /rooms' => 'Create room (admin only)',
                'GET /bookings' => 'List bookings (requires token)',
                'GET /bookings/{id}' => 'Get booking details (requires token)',
                'POST /bookings' => 'Create booking (requires token)',
                'PUT /bookings/{id}/status' => 'Update booking status (admin only)',
                'DELETE /bookings/{id}' => 'Delete booking (requires token)',
                'GET /timeslots' => 'List all timeslots',
                'GET /timeslots/{id}' => 'Get timeslot details',
                'POST /timeslots' => 'Create timeslot (admin only)',
                'PUT /timeslots/{id}' => 'Update timeslot (admin only)',
                'DELETE /timeslots/{id}' => 'Delete timeslot (admin only)'
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }

    sendError('Route not found', 404);

} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}
