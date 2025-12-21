<?php
require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../models/User.php';

class ApiAuthController extends ApiController
{
    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateRequired($data, ['name', 'email', 'password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $this->error('Invalid email format', 400);
        }

        $existing = User::findByEmail($email);
        if ($existing) {
            $this->error('Email already registered', 400);
        }

        $name = trim($data['name']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role_id = 2; // regular user

        $success = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $role_id
        ]);

        if (!$success) {
            $this->error('Failed to create user', 500);
        }

        $user = User::findByEmail($email);
        $token = $this->generateToken($user['id'], $user['role_id']);

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.register', ['email' => $email], $user['id']);

        $this->success([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $user['role_id']
            ]
        ], 'Registration successful', 201);
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $errors = $this->validateRequired($data, ['email', 'password']);
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $this->error('Invalid email format', 400);
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            $this->error('Invalid credentials', 401);
        }

        $token = $this->generateToken($user['id'], $user['role_id']);

        require_once __DIR__ . '/../helpers/logger.php';
        activity_log('api.login', ['email' => $email], $user['id']);

        $this->success([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $user['role_id']
            ]
        ], 'Login successful');
    }

    public function me()
    {
        $user = $this->requireAuth();
        
        $this->success([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $user['role_id']
            ]
        ], 'User retrieved successfully');
    }
}
