<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Get admin info including last login
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.last_login,
            u.created_at,
            COUNT(al.id) as total_logins
        FROM users u
        LEFT JOIN admin_logs al ON u.id = al.admin_id AND al.action = 'login'
        WHERE u.id = ? AND u.role = 'admin'
        GROUP BY u.id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        http_response_code(404);
        echo json_encode(['error' => 'admin_not_found']);
        exit;
    }
    
    // Get recent admin activity
    $stmt = $pdo->prepare("
        SELECT action, created_at, ip_address
        FROM admin_logs 
        WHERE admin_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_activity = $stmt->fetchAll();
    
    // Get system stats for admin
    $stats = [];
    
    // Total bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings");
    $stmt->execute();
    $stats['total_bookings'] = $stmt->fetch()['count'];
    
    // Pending bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_bookings'] = $stmt->fetch()['count'];
    
    // Total revenue
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed'");
    $stmt->execute();
    $stats['total_revenue'] = $stmt->fetch()['total'];
    
    // Monthly revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as total 
        FROM bookings 
        WHERE status = 'confirmed' 
        AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute();
    $stats['monthly_revenue'] = $stmt->fetch()['total'];
    
    // Available rooms
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE available = 1");
    $stmt->execute();
    $stats['available_rooms'] = $stmt->fetch()['count'];
    
    // Total customers
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $stmt->execute();
    $stats['total_customers'] = $stmt->fetch()['count'];
    
    echo json_encode([
        'admin' => $admin,
        'recent_activity' => $recent_activity,
        'stats' => $stats,
        'last_login' => $admin['last_login']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error', 'message' => $e->getMessage()]);
}
?>
