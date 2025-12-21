<?php
// Site constants
define('APP_NAME', 'Mini Room Booking');
define('BASE_PATH', '/'); // adjust if hosted in subfolder

// DB constants - adjust to your local setup
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'room_booking');
define('DB_USER', 'root');
define('DB_PASS', '');

// Other
define('SESSION_NAME', 'mini_booking_session');
define('CSRF_TOKEN_KEY', '_csrf_token');

// JWT Secret - CHANGE THIS IN PRODUCTION!
define('JWT_SECRET', 'your-secret-key-change-this-in-production-booking-room-2024');
