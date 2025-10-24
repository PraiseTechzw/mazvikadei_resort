<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized', 'message' => 'Please login to access user details']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Get user details
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            email,
            phone,
            address,
            date_of_birth,
            created_at,
            last_login
        FROM users 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'user_not_found', 'message' => 'User not found']);
        exit;
    }
    
    // Get user's booking history
    $stmt = $pdo->prepare("
        SELECT 
            id,
            booking_reference,
            type,
            total_amount,
            status,
            created_at,
            check_in_date,
            check_out_date
        FROM bookings 
        WHERE customer_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_bookings = $stmt->fetchAll();
    
    // Get user's preferences if available
    $preferences = null;
    if ($user['preferences']) {
        $preferences = json_decode($user['preferences'], true);
    }
    
    echo json_encode([
        'user' => $user,
        'recent_bookings' => $recent_bookings,
        'preferences' => $preferences,
        'member_since' => date('F Y', strtotime($user['created_at'])),
        'last_login_formatted' => $user['last_login'] ? date('F j, Y \a\t g:i A', strtotime($user['last_login'])) : 'First time'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error', 'message' => $e->getMessage()]);
}
?>
