<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get query parameters
    $category_id = $_GET['category_id'] ?? null;
    $active = $_GET['active'] ?? 'true';
    
    $sql = "SELECT a.*, ac.name as category_name 
            FROM activities a 
            LEFT JOIN activity_categories ac ON a.category_id = ac.id 
            WHERE 1=1";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($active === 'true') {
        $sql .= " AND a.status = 'active'";
    }
    
    $sql .= " ORDER BY a.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();
    
    // Get activity categories
    $stmt = $pdo->query("SELECT * FROM activity_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'activities' => $activities,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
