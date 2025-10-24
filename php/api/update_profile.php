<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to update profile']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { 
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'No input data']); 
    exit; 
}

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');

// Validation
if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already taken by another user']);
        exit;
    }
    
    // Update user profile
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
    
    if ($result) {
        // Update session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>