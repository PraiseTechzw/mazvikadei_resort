<?php
require_once __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

// Simple session auth check - for demo only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT * FROM bookings ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>