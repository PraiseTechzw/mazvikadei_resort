<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get query parameters
    $category_id = $_GET['category_id'] ?? null;
    $active = $_GET['active'] ?? 'true';
    
    $sql = "SELECT e.*, ec.name as category_name 
            FROM events e 
            LEFT JOIN event_categories ec ON e.category_id = ec.id 
            WHERE 1=1";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND e.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($active === 'true') {
        $sql .= " AND e.status = 'active'";
    }
    
    $sql .= " ORDER BY e.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    
    // Get event categories
    $stmt = $pdo->query("SELECT * FROM event_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'events' => $events,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
