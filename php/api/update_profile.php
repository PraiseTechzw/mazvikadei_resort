<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized', 'message' => 'Please login to update profile']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed', 'message' => 'Only POST method allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_input', 'message' => 'Invalid JSON input']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Validate required fields
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $address = trim($input['address'] ?? '');
    
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_fields', 'message' => 'Name and email are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_email', 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'email_taken', 'message' => 'Email is already taken by another user']);
        exit;
    }
    
    // Update user profile
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $phone, $address, $_SESSION['user_id']]);
    
    // Update session data
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error', 'message' => $e->getMessage()]);
}
?>
