<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get filter parameters
    $date_from = $_GET['date_from'] ?? date('Y-m-01');
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    $type = $_GET['type'] ?? 'overview';
    $group_by = $_GET['group_by'] ?? 'day';
    
    $reports = [];
    
    // Key Metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_booking_value
        FROM bookings 
        WHERE created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$date_from, $date_to]);
    $metrics = $stmt->fetch();
    
    // Occupancy Rate
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_rooms,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms
        FROM rooms
    ");
    $occupancy = $stmt->fetch();
    $occupancy_rate = $occupancy['total_rooms'] > 0 ? 
        ($occupancy['occupied_rooms'] / $occupancy['total_rooms']) * 100 : 0;
    
    // Customer Satisfaction (mock data)
    $customer_satisfaction = 4.5;
    
    // Repeat Customers
    $stmt = $pdo->query("
        SELECT COUNT(*) as repeat_customers
        FROM (
            SELECT customer_id 
            FROM bookings 
            GROUP BY customer_id 
            HAVING COUNT(*) > 1
        ) as repeat_customers
    ");
    $repeat_customers = $stmt->fetch()['repeat_customers'];
    
    $reports['metrics'] = [
        'total_revenue' => $metrics['total_revenue'] ?? 0,
        'total_bookings' => $metrics['total_bookings'] ?? 0,
        'average_booking_value' => $metrics['average_booking_value'] ?? 0,
        'occupancy_rate' => $occupancy_rate,
        'customer_satisfaction' => $customer_satisfaction,
        'repeat_customers' => $repeat_customers
    ];
    
    // Revenue Trends
    $date_format = $group_by === 'day' ? '%Y-%m-%d' : 
                   ($group_by === 'week' ? '%Y-%u' : 
                   ($group_by === 'month' ? '%Y-%m' : '%Y'));
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, ?) as date,
            SUM(total_amount) as revenue
        FROM bookings 
        WHERE created_at BETWEEN ? AND ?
        AND status IN ('confirmed', 'checked_in', 'checked_out')
        GROUP BY DATE_FORMAT(created_at, ?)
        ORDER BY date
    ");
    $stmt->execute([$date_format, $date_from, $date_to, $date_format]);
    $reports['revenue_trends'] = $stmt->fetchAll();
    
    // Booking Trends
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, ?) as date,
            COUNT(*) as count
        FROM bookings 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(created_at, ?)
        ORDER BY date
    ");
    $stmt->execute([$date_format, $date_from, $date_to, $date_format]);
    $reports['booking_trends'] = $stmt->fetchAll();
    
    // Occupancy Data
    $reports['occupancy'] = [
        'occupied' => $occupancy['occupied_rooms'],
        'available' => $occupancy['total_rooms'] - $occupancy['occupied_rooms']
    ];
    
    // Demographics (mock data)
    $reports['demographics'] = [
        ['category' => 'Local', 'count' => 45],
        ['category' => 'Regional', 'count' => 30],
        ['category' => 'International', 'count' => 25]
    ];
    
    // Popular Activities
    $stmt = $pdo->prepare("
        SELECT 
            a.title as name,
            COUNT(b.id) as bookings
        FROM activities a
        LEFT JOIN bookings b ON JSON_EXTRACT(b.items_json, '$[*].activity_id') LIKE CONCAT('%', a.id, '%')
        AND b.created_at BETWEEN ? AND ?
        GROUP BY a.id, a.title
        ORDER BY bookings DESC
        LIMIT 5
    ");
    $stmt->execute([$date_from, $date_to]);
    $reports['popular_activities'] = $stmt->fetchAll();
    
    // Payment Methods
    $stmt = $pdo->prepare("
        SELECT 
            payment_method as method,
            COUNT(*) as count
        FROM bookings 
        WHERE created_at BETWEEN ? AND ?
        AND payment_method IS NOT NULL
        GROUP BY payment_method
    ");
    $stmt->execute([$date_from, $date_to]);
    $reports['payment_methods'] = $stmt->fetchAll();
    
    // Top Performing Rooms
    $stmt = $pdo->prepare("
        SELECT 
            r.title,
            COUNT(b.id) as bookings,
            COALESCE(SUM(b.total_amount), 0) as revenue,
            (COUNT(b.id) / 30) * 100 as occupancy_rate,
            4.5 as average_rating
        FROM rooms r
        LEFT JOIN bookings b ON JSON_EXTRACT(b.items_json, '$[*].room_id') LIKE CONCAT('%', r.id, '%')
        AND b.created_at BETWEEN ? AND ?
        GROUP BY r.id, r.title
        ORDER BY bookings DESC
        LIMIT 5
    ");
    $stmt->execute([$date_from, $date_to]);
    $reports['top_rooms'] = $stmt->fetchAll();
    
    // Recent Activity (mock data)
    $reports['recent_activity'] = [
        [
            'date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'activity' => 'New Booking',
            'user' => 'John Doe',
            'details' => 'Room booking #MZ2024001'
        ],
        [
            'date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'activity' => 'Payment Received',
            'user' => 'System',
            'details' => 'Payment for booking #MZ2024000'
        ],
        [
            'date' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'activity' => 'Room Check-in',
            'user' => 'Jane Smith',
            'details' => 'Guest checked into Ocean Suite'
        ],
        [
            'date' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            'activity' => 'Activity Booking',
            'user' => 'Mike Johnson',
            'details' => 'Boat cruising activity booked'
        ],
        [
            'date' => date('Y-m-d H:i:s', strtotime('-5 hours')),
            'activity' => 'Customer Registration',
            'user' => 'System',
            'details' => 'New customer registered'
        ]
    ];
    
    echo json_encode($reports);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db', 'message' => $e->getMessage()]);
}
?>
