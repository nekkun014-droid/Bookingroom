<?php
// Front controller (simple)
session_start();
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/csrf.php';

// Auto-login from remember-me cookie (selector:token) if session not present
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        $selector = $parts[0];
        $token = $parts[1];
        require_once __DIR__ . '/../app/models/PersistentLogin.php';
        require_once __DIR__ . '/../app/models/User.php';
        $rec = PersistentLogin::findBySelector($selector);
        if ($rec) {
            // check expiry
            if (strtotime($rec['expires_at']) >= time()) {
                $token_hash = hash('sha256', $token);
                if (hash_equals($rec['token_hash'], $token_hash)) {
                    // valid: log user in
                    $user = User::find($rec['user_id']);
                    if ($user) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role_id'] = $user['role_id'];
                        $_SESSION['user_name'] = $user['name'];
                            // log auto-login via remember-me
                            require_once __DIR__ . '/../app/helpers/logger.php';
                            activity_log('remember.auto_login', ['selector'=>$selector], $user['id']);
                        // rotate token: generate new token and update DB + cookie
                        $newToken = bin2hex(random_bytes(33));
                        $newHash = hash('sha256', $newToken);
                        $newExpiry = time() + (30 * 24 * 60 * 60);
                        PersistentLogin::updateToken($selector, $newHash, date('Y-m-d H:i:s', $newExpiry));
                        $cookieVal = $selector . ':' . $newToken;
                        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                        setcookie('remember_me', $cookieVal, [
                            'expires' => $newExpiry,
                            'path' => '/',
                            'domain' => '',
                            'secure' => $secure,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                            // log token rotation
                            require_once __DIR__ . '/../app/helpers/logger.php';
                            activity_log('remember.rotated', ['selector'=>$selector,'new_expires'=>date('Y-m-d H:i:s',$newExpiry)], $user['id']);
                    }
                } else {
                    // invalid token: remove record & cookie
                    PersistentLogin::deleteBySelector($selector);
                        require_once __DIR__ . '/../app/helpers/logger.php';
                        activity_log('remember.invalid_token', ['selector'=>$selector], null);
                    setcookie('remember_me', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }
            } else {
                // expired: cleanup
                PersistentLogin::deleteBySelector($selector);
                    require_once __DIR__ . '/../app/helpers/logger.php';
                    activity_log('remember.expired', ['selector'=>$selector], null);
                setcookie('remember_me', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        } else {
            // unknown selector: clear cookie
                require_once __DIR__ . '/../app/helpers/logger.php';
                activity_log('remember.unknown_selector', ['selector'=>$selector], null);
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
}

// simple autoloader for models/controllers
spl_autoload_register(function ($class) {
    $paths = [__DIR__ . '/../app/models/', __DIR__ . '/../app/controllers/'];
    foreach ($paths as $p) {
        $file = $p . $class . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// flash helper
function flash($key = null, $message = null)
{
    // If called with no args, return all flash messages and clear them
    if ($key === null) {
        $all = $_SESSION['flash'] ?? [];
        // clear stored flashes so they don't persist across requests
        unset($_SESSION['flash']);
        return $all;
    }
    // If called as getter for a specific key, return and unset that key
    if ($message === null) {
        $m = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    // Setter: store flash message
    $_SESSION['flash'][$key] = $message;
}

$action = $_GET['action'] ?? 'home';

// Simple routing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $ctrl = new AuthController();
        $ctrl->login($_POST);
        exit;
    }
    if ($action === 'logout') {
        $ctrl = new AuthController();
        $ctrl->logout();
        exit;
    }
    if ($action === 'create_booking') {
        $ctrl = new BookingController();
        $ctrl->store($_POST);
        exit;
    }
    if ($action === 'booking_action') {
        $ctrl = new BookingController();
        $ctrl->adminAction($_POST);
        exit;
    }
    if ($action === 'timeslot_create') {
        $ctrl = new TimeslotController();
        $ctrl->store($_POST);
        exit;
    }
    if ($action === 'timeslot_update') {
        $ctrl = new TimeslotController();
        $ctrl->update($_POST);
        exit;
    }
    if ($action === 'timeslot_delete') {
        $ctrl = new TimeslotController();
        $ctrl->delete($_POST);
        exit;
    }
    if ($action === 'password_send') {
        $ctrl = new AuthController();
        $ctrl->sendReset($_POST);
        exit;
    }
    if ($action === 'password_update') {
        $ctrl = new AuthController();
        $ctrl->performReset($_POST);
        exit;
    }
    // CSV exports (GET also allowed but we handle via POST or GET)
    if ($action === 'export_rooms_csv') {
        $ctrl = new RoomController();
        $ctrl->exportCsv();
        exit;
    }
    if ($action === 'export_bookings_csv') {
        $ctrl = new BookingController();
        $ctrl->exportCsv();
        exit;
    }
    if ($action === 'export_timeslots_csv') {
        $ctrl = new TimeslotController();
        $ctrl->exportCsv();
        exit;
    }
}

// pages
ob_start();
if ($action === 'login') {
    require __DIR__ . '/../app/views/login.php';
} elseif ($action === 'password_request') {
    require __DIR__ . '/../app/views/auth/password_request.php';
} elseif ($action === 'password_reset') {
    require __DIR__ . '/../app/views/auth/password_reset.php';
} elseif ($action === 'dashboard') {
    require __DIR__ . '/../app/views/dashboard.php';
} elseif ($action === 'rooms') {
    $ctrl = new RoomController();
    $ctrl->index();
} elseif ($action === 'bookings') {
    $ctrl = new BookingController();
    $ctrl->index();
} elseif ($action === 'timeslots') {
    $ctrl = new TimeslotController();
    $ctrl->index();
} else {
    require __DIR__ . '/../app/views/home.php';
}
$content = ob_get_clean();
require __DIR__ . '/../app/views/templates/layout.php';
