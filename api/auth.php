<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt_config.php';
require_once __DIR__ . '/../config/database.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header('Content-Type: application/json');

// Function to generate JWT token
function generateToken($userId, $isRefresh = false) {
    $issuedAt = time();
    $expiration = $isRefresh ? JWT_REFRESH_EXPIRATION : JWT_EXPIRATION;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $issuedAt + $expiration,
        'user_id' => $userId,
        'type' => $isRefresh ? 'refresh' : 'access'
    ];
    
    return JWT::encode($payload, JWT_SECRET_KEY, JWT_ALGORITHM);
}

// Function to validate token
function validateToken($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

// Login endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/auth/login') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE username = ?');
        $stmt->execute([$data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($data['password'], $user['password'])) {
            // Generate tokens
            $accessToken = generateToken($user['id']);
            $refreshToken = generateToken($user['id'], true);
            
            // Set refresh token in HTTP-only cookie
            setcookie(
                'refresh_token',
                $refreshToken,
                [
                    'expires' => time() + JWT_REFRESH_EXPIRATION,
                    'path' => '/',
                    'secure' => COOKIE_SECURE,
                    'httponly' => COOKIE_HTTPONLY,
                    'samesite' => COOKIE_SAMESITE
                ]
            );
            
            echo json_encode([
                'access_token' => $accessToken,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

// Refresh token endpoint
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/auth/refresh') {
    if (!isset($_COOKIE['refresh_token'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Refresh token not found']);
        exit;
    }
    
    $decoded = validateToken($_COOKIE['refresh_token']);
    if (!$decoded || $decoded->type !== 'refresh') {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid refresh token']);
        exit;
    }
    
    $accessToken = generateToken($decoded->user_id);
    echo json_encode(['access_token' => $accessToken]);
}

// Logout endpoint
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/auth/logout') {
    setcookie('refresh_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => COOKIE_SECURE,
        'httponly' => COOKIE_HTTPONLY,
        'samesite' => COOKIE_SAMESITE
    ]);
    
    echo json_encode(['message' => 'Logged out successfully']);
}

// Middleware to protect routes
function authenticateRequest() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No token provided']);
        exit;
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded = validateToken($token);
    
    if (!$decoded || $decoded->type !== 'access') {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }
    
    return $decoded->user_id;
}
?> 