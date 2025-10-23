<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get dashboard statistics
    $stats = [];
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $stats['total_bookings'] = $stmt->fetch()['total'];
    
    // Pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $stats['pending_bookings'] = $stmt->fetch()['total'];
    
    // Confirmed bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
    $stats['confirmed_bookings'] = $stmt->fetch()['total'];
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM bookings WHERE status IN ('confirmed', 'checked_in', 'checked_out')");
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Monthly revenue
    $stmt = $pdo->query("
        SELECT SUM(total_amount) as total 
        FROM bookings 
        WHERE status IN ('confirmed', 'checked_in', 'checked_out') 
        AND MONTH(created_at) = MONTH(NOW()) 
        AND YEAR(created_at) = YEAR(NOW())
    ");
    $stats['monthly_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Available rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms WHERE status = 'available'");
    $stats['available_rooms'] = $stmt->fetch()['total'];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $stats['total_customers'] = $stmt->fetch()['total'];
    
    // Recent bookings
    $stmt = $pdo->query("
        SELECT b.*, u.name as customer_name 
        FROM bookings b 
        LEFT JOIN users u ON b.customer_id = u.id 
        ORDER BY b.created_at DESC 
        LIMIT 10
    ");
    $recent_bookings = $stmt->fetchAll();
    
    // Booking trends (last 7 days)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY date
    ");
    $booking_trends = $stmt->fetchAll();
    
    // Revenue trends (last 7 days)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, SUM(total_amount) as revenue 
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND status IN ('confirmed', 'checked_in', 'checked_out')
        GROUP BY DATE(created_at) 
        ORDER BY date
    ");
    $revenue_trends = $stmt->fetchAll();
    
    echo json_encode([
        'stats' => $stats,
        'recent_bookings' => $recent_bookings,
        'booking_trends' => $booking_trends,
        'revenue_trends' => $revenue_trends
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
