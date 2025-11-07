<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/csrf.php';

class AuthController
{
    public function login($data)
    {
        // CSRF
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=login');
            return;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash']['error'] = 'Email or password incorrect.';
            header('Location: ?action=login');
            return;
        }
    // success
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['user_name'] = $user['name'];
        // log login
        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('login', null, $user['id']);

    // handle remember-me
        if (!empty($data['remember'])) {
            require_once __DIR__ . '/../models/PersistentLogin.php';
            // generate selector and token
            $selector = bin2hex(random_bytes(9)); // 18 hex chars
            $token = bin2hex(random_bytes(33)); // 66 hex chars
            $token_hash = hash('sha256', $token);
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            $expires_at = date('Y-m-d H:i:s', $expiry);
            // insert
            PersistentLogin::create($user['id'], $selector, $token_hash, $expires_at);
            // set cookie: selector:token (raw token used only in cookie)
            $cookieVal = $selector . ':' . $token;
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie('remember_me', $cookieVal, [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            // log remember creation
            require_once __DIR__ . '/../helpers/logger.php';
            activity_log('remember_created', ['selector'=>$selector,'expires_at'=>$expires_at], $user['id']);
        }

        $_SESSION['flash']['success'] = 'Welcome back, ' . htmlspecialchars($user['name']);
        header('Location: ?action=dashboard');
    }

    public function logout()
    {
        // log logout before destroying session if possible
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) {
            require_once __DIR__ . '/../helpers/logger.php';
            activity_log('logout', null, $uid);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        // remove persistent login cookie & DB record if present
        if (!empty($_COOKIE['remember_me'])) {
            $parts = explode(':', $_COOKIE['remember_me']);
            if (count($parts) === 2) {
                $selector = $parts[0];
                require_once __DIR__ . '/../models/PersistentLogin.php';
                PersistentLogin::deleteBySelector($selector);
                // log remember deletion
                require_once __DIR__ . '/../helpers/logger.php';
                activity_log('remember_deleted', ['selector'=>$selector], $uid);
            }
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        session_destroy();
        header('Location: ?');
    }

    // show reset request form (just via routing to view)

    public function sendReset($data)
    {
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=password_request');
            return;
        }
        $email = trim($data['email'] ?? '');
        if (!$email) {
            $_SESSION['flash']['error'] = 'Please enter your email.';
            header('Location: ?action=password_request');
            return;
        }
        $user = User::findByEmail($email);
        if (!$user) {
            // don't reveal whether email exists
            $_SESSION['flash']['success'] = 'Jika email terdaftar, tautan reset telah dikirim. Silakan periksa kotak masuk atau spam Anda.';
            header('Location: ?action=password_request');
            return;
        }
        // cleanup previous tokens for this email
        require_once __DIR__ . '/../models/PasswordReset.php';
        PasswordReset::deleteByEmail($email);
        // generate token
        $token = bin2hex(random_bytes(16));
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 60*60); // 1 hour
        PasswordReset::create($email, $token_hash, $expires);
        // Build reset URL (development: show link in session so dev can click it)
        $resetUrl = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . dirname($_SERVER['SCRIPT_NAME']) . '/?action=password_reset&token=' . $token;
    $_SESSION['flash']['success'] = 'Jika email terdaftar, tautan reset telah dikirim. Untuk pengembangan, tautan juga ditampilkan di bawah.';
        // For dev/testing store the link in session so developers can click it locally
        if (defined('TESTING') && TESTING) {
            $_SESSION['reset_link'] = $resetUrl;
        }
        // log password reset request (do not include token)
        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('password_reset.request', ['email'=>$email], $user['id']);
        header('Location: ?action=password_request');
    }

    public function performReset($data)
    {
        if (!validate_csrf($data['_csrf'] ?? '')) {
            $_SESSION['flash']['error'] = 'Invalid CSRF token.';
            header('Location: ?action=password_request');
            return;
        }
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        $password_confirm = $data['password_confirm'] ?? '';
        if (!$token || !$password || !$password_confirm) {
            $_SESSION['flash']['error'] = 'All fields are required.';
            header('Location: ?action=password_reset&token=' . urlencode($token));
            return;
        }
        if ($password !== $password_confirm) {
            $_SESSION['flash']['error'] = 'Passwords do not match.';
            header('Location: ?action=password_reset&token=' . urlencode($token));
            return;
        }
        if (strlen($password) < 6) {
            $_SESSION['flash']['error'] = 'Password must be at least 6 characters.';
            header('Location: ?action=password_reset&token=' . urlencode($token));
            return;
        }
        require_once __DIR__ . '/../models/PasswordReset.php';
        $pr = PasswordReset::findByToken($token);
        if (!$pr) {
            $_SESSION['flash']['error'] = 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.';
            header('Location: ?action=password_request');
            return;
        }
        // find user by email
        $user = User::findByEmail($pr['email']);
        if (!$user) {
            $_SESSION['flash']['error'] = 'User not found.';
            header('Location: ?action=password_request');
            return;
        }
        // update password
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        // remove used token
        PasswordReset::deleteByToken($token);
        // log password reset performed
        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('password_reset.perform', ['user_id'=>$user['id']], $user['id']);
        $_SESSION['flash']['success'] = 'Password updated. You may now login.';
        header('Location: ?action=login');
    }
}
