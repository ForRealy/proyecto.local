<?php
require_once __DIR__ . '/../api/auth.php';

function requireAuth() {
    $userId = authenticateRequest();
    
    // Check if user has admin role
    global $pdo;
    $stmt = $pdo->prepare('SELECT role FROM Users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
    
    return $userId;
}

// Function to check if user is authenticated (for non-API routes)
function isAuthenticated() {
    if (!isset($_COOKIE['refresh_token'])) {
        return false;
    }
    
    $decoded = validateToken($_COOKIE['refresh_token']);
    return $decoded && $decoded->type === 'refresh';
}

// Function to check if user is admin (for non-API routes)
function isAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    $decoded = validateToken($_COOKIE['refresh_token']);
    global $pdo;
    $stmt = $pdo->prepare('SELECT role FROM Users WHERE id = ?');
    $stmt->execute([$decoded->user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && $user['role'] === 'admin';
}
?> 