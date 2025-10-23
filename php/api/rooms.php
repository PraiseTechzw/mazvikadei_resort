<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get query parameters
    $category_id = $_GET['category_id'] ?? null;
    $available = $_GET['available'] ?? null;
    $check_in = $_GET['check_in'] ?? null;
    $check_out = $_GET['check_out'] ?? null;
    
    $sql = "SELECT r.*, rc.name as category_name 
            FROM rooms r 
            LEFT JOIN room_categories rc ON r.category_id = rc.id 
            WHERE 1=1";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND r.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($available === 'true') {
        $sql .= " AND r.status = 'available'";
    }
    
    // Check availability for specific dates
    if ($check_in && $check_out) {
        $sql .= " AND r.id NOT IN (
            SELECT DISTINCT ra.room_id 
            FROM room_availability ra 
            WHERE ra.date >= ? AND ra.date < ? AND ra.status = 'booked'
        )";
        $params[] = $check_in;
        $params[] = $check_out;
    }
    
    $sql .= " ORDER BY r.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    // Get room categories
    $stmt = $pdo->query("SELECT * FROM room_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'rooms' => $rooms,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
