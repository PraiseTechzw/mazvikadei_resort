<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT * FROM bookings ORDER BY created_at DESC LIMIT 500');
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>