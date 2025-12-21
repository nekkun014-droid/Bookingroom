<?php

class ApiController
{
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    protected function success($data = [], $message = 'Success', $statusCode = 200)
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function error($message = 'Error', $statusCode = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        $this->jsonResponse($response, $statusCode);
    }

    protected function unauthorized($message = 'Unauthorized')
    {
        $this->error($message, 401);
    }

    protected function notFound($message = 'Resource not found')
    {
        $this->error($message, 404);
    }

    protected function forbidden($message = 'Forbidden')
    {
        $this->error($message, 403);
    }

    protected function validateRequired($data, $fields)
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        return $errors;
    }

    protected function getAuthUser()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (empty($token)) {
            return null;
        }

        require_once __DIR__ . '/../models/User.php';
        $decoded = $this->decodeToken($token);
        
        if (!$decoded || !isset($decoded['user_id'])) {
            return null;
        }

        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return null;
        }

        return User::findById($decoded['user_id']);
    }

    protected function requireAuth()
    {
        $user = $this->getAuthUser();
        if (!$user) {
            $this->unauthorized('Invalid or expired token');
        }
        return $user;
    }

    protected function requireAdmin()
    {
        $user = $this->requireAuth();
        if ($user['role_id'] != 1) {
            $this->forbidden('Admin access required');
        }
        return $user;
    }

    protected function generateToken($userId, $roleId)
    {
        $payload = [
            'user_id' => $userId,
            'role_id' => $roleId,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // 7 days
        ];
        
        $secret = $this->getSecret();
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload_encoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload_encoded", $secret);
        
        return "$header.$payload_encoded.$signature";
    }

    protected function decodeToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload_encoded, $signature] = $parts;
        $secret = $this->getSecret();
        $expected_signature = hash_hmac('sha256', "$header.$payload_encoded", $secret);

        if (!hash_equals($expected_signature, $signature)) {
            return null;
        }

        $payload = json_decode(base64_decode($payload_encoded), true);
        return $payload;
    }

    private function getSecret()
    {
        return defined('JWT_SECRET') ? JWT_SECRET : 'your-secret-key-change-this-in-production';
    }
}
